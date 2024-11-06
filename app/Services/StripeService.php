<?php

namespace App\Services;

use App\Traits\ConsumesExternalServiceTrait;
use App\Http\Controllers\Admin\Webhooks\PaymentWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\Statistics\UserService;
use App\Events\PaymentReferrerBonus;
use App\Events\PaymentProcessed;
use App\Models\Payment;
use App\Models\Subscriber;
use App\Models\PrepaidPlan;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use Stripe\Stripe;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccess;
use App\Mail\NewPaymentNotification;
use App\Services\HelperService;
use Exception;

class StripeService 
{
    use ConsumesExternalServiceTrait;

    protected $baseURI;
    protected $key;
    protected $secret;
    protected $promocode;
    private $api;

    /**
     * Stripe payment processing, unless you are familiar with 
     * Stripe's REST API, we recommend not to modify core payment processing functionalities here.
     * Part that are writing data to the database can be edited as you prefer.
     */
    public function __construct()
    {
        $this->api = new UserService();

        $verify = $this->api->verify_license();

        if($verify['status']!=true){
            return false;
        }
    }


    /**
     * Initate subscription plan payment process
     *
     * @return \Illuminate\Http\Response
     */
    public function handlePaymentSubscription(Request $request, SubscriptionPlan $id)
    {
        if (!$id->stripe_gateway_plan_id) {
            toastr()->error(__('Stripe plan id is not set. Please contact the support team.'));
            return redirect()->back();
        }        

        session()->put('plan_id', $id->id);
        session()->put('type', 'subscription');
        session()->put('amount', $request->value);

        return view('user.plans.stripe');
    }

    /**
     * Initate prepaid plan payment process
     *
     * @return \Illuminate\Http\Response
     */
    public function handlePaymentPrePaid(Request $request, $id, $type)
    {
        if ($request->type == 'lifetime') {
            $plan = SubscriptionPlan::where('id', $id)->first();
            $type = 'lifetime';
        } else {
            $plan = PrepaidPlan::where('id', $id)->first();
            $type = 'prepaid';
        }

        session()->put('plan_id', $plan);
        session()->put('type', $type);
        session()->put('amount', $request->value);

        return view('user.plans.stripe');
    }


    /**
     * Process stripe payment
     *
     * @return \Illuminate\Http\Response
     */
    public function processStripe() 
    {
        if (session()->has('type')) {
            $plan = session()->get('plan_id');
            $type = session()->get('type'); 
            $total_value = session()->get('amount'); 
        }

        if ($type == 'subscription') {
            $sub = SubscriptionPlan::where('id', $plan)->first();
        }
      
        Stripe::setApiKey(config('services.stripe.api_secret'));

        try {
            $amount = number_format((float)$total_value, 2, '.', '')  * 100;
            $payment = new PaymentWebhook();
            $process = $payment->download();
            if (!$process['status']) return false;

            if ($type == 'prepaid' || $type == 'lifetime') {
                $session = \Stripe\Checkout\Session::create([
                    'customer_email' => auth()->user()->email,
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' => $plan->currency,
                                'product_data' => [
                                    'name' => $plan->plan_name . "Payment",
                                ],
                                'unit_amount' => $amount,
                            ],
                            'quantity' => 1,
                        ]
                    ],
                    'mode' => 'payment',
                    'success_url' => route('user.payments.approved'),
                    'cancel_url' => route('user.payments.stripe.cancel'),
                ]);

                if (!is_null($session->payment_intent)) {
                    session()->put('paymentIntentID', $session->payment_intent);
                } else {
                    session()->put('paymentIntentID', $session->id);
                }

            } else {
               
                $session = \Stripe\Checkout\Session::create([
                    'line_items' => [[ 
                        'price' => $sub->stripe_gateway_plan_id, 
                        'quantity' => 1
                    ]], 
                    'mode' => 'subscription',
                    'success_url' => route('user.payments.subscription.stripe', ['plan_id' => $sub->id]),
                    'cancel_url' => route('user.payments.stripe.cancel'),
                ]);

                session()->put('subscriptionID', $session->id);

            }            

            

        } catch (\Exception $e) {
            toastr()->error(__('Stripe authentication error, verify your stripe settings first ' . $e->getMessage()));
            return redirect()->back();
        } 

        return response()->json(['id' => $session->id, 'status' => 200]);
    }


    /**
     * Process stripe pament cancelation
     *
     * @return \Illuminate\Http\Response
    */
    public function processCancel() 
    {
        toastr()->warning(__('Stripe payment has been cancelled'));
        return redirect()->route('user.plans');
    }


    /**
     * Handle prepaid approvals
     *
     * @return \Illuminate\Http\Response
     */
    public function handleApproval(Request $request)
    {
        $paymentIntentID = session()->get('paymentIntentID');
        $plan = session()->get('plan_id');
        $type = session()->get('type');
        $amount = session()->get('amount');     
    
        if (config('payment.referral.enabled') == 'on') {
            if (config('payment.referral.payment.policy') == 'first') {
                if (Payment::where('user_id', auth()->user()->id)->where('status', 'completed')->exists()) {
                    /** User already has at least 1 payment */
                } else {
                    event(new PaymentReferrerBonus(auth()->user(), $paymentIntentID, $amount, 'Stripe'));
                }
            } else {
                event(new PaymentReferrerBonus(auth()->user(), $paymentIntentID, $amount, 'Stripe'));
            }
        }

        if ($type == 'lifetime') {

            $subscription_id = Str::random(10);
            $days = 18250;

            HelperService::registerSubscriber($plan, 'Stripe', 'Active', $subscription_id, $days);
        }

        $payment = HelperService::registerPayment($type, $plan->id, $paymentIntentID, $amount, 'Stripe', 'completed');

        HelperService::registerCredits($type, $plan->id);

        event(new PaymentProcessed(auth()->user()));
        $order_id = $paymentIntentID;

        try {
            $admin = User::where('group', 'admin')->first();
            
            Mail::to($admin)->send(new NewPaymentNotification($payment));
            Mail::to($request->user())->send(new PaymentSuccess($payment));
        } catch (Exception $e) {
            \Log::info('SMTP settings are not setup to send payment notifications via email');
        }

        return view('user.plans.success', compact('plan', 'order_id'));
        
    }


    public function stopSubscription($subscriptionID)
    {
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.api_secret'));
            $stripe->subscriptions->cancel(
                $subscriptionID,
                []
            );
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
        }  

       return true;
    }


    public function validateSubscriptions(Request $request)
    {
        if (session()->has('subscriptionID')) {
            $subscriptionID = session()->get('subscriptionID');

            session()->forget('subscriptionID');

            return $request->subscription_id == $subscriptionID;
        }

        return false;
    }


}