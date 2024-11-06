<?php

namespace App\Services;

use App\Traits\ConsumesExternalServiceTrait;
use App\Events\PaymentReferrerBonus;
use Spatie\Backup\Listeners\Listener;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\Statistics\UserService;
use App\Events\PaymentProcessed;
use App\Models\Payment;
use App\Models\PrepaidPlan;
use App\Models\SubscriptionPlan;
use App\Models\Subscriber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccess;
use App\Mail\NewPaymentNotification;
use App\Services\HelperService;
use Exception;


class PaystackService 
{
    use ConsumesExternalServiceTrait;

    protected $baseURI;
    protected $apiSecret;
    private $api;

    /**
     * Paypal payment processing, unless you are familiar with 
     * Paypal's REST API, we recommend not to modify core payment processing functionalities here.
     * Part that are writing data to the database can be edited as you prefer.
     */
    public function __construct()
    {
        $this->api = new UserService();

        $verify = $this->api->verify_license();

        if($verify['status']!=true){
            return false;
        }

        $this->baseURI = config('services.paystack.base_uri');
        $this->apiSecret = config('services.paystack.api_secret');
    }


    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers['Authorization'] = $this->resolveAccessToken();
    }


    public function decodeResponse($response)
    {
        return json_decode($response);
    }


    public function resolveAccessToken()
    {
        if ($this->api->api_url != 'https://license.berkine.space/') {
            return;
        }

        return "Bearer {$this->apiSecret}";
    }


    public function handlePaymentSubscription(Request $request, SubscriptionPlan $id)
    {   
        if (!$id->paystack_gateway_plan_id) {
            toastr()->error(__('Paystack plan id is not set. Please contact the support team'));
            return redirect()->back();
        } 

        $price = intval($id->price * 100);

        $listener = new Listener();
        $process = $listener->download();
        if (!$process['status']) return false;

        try {
            $subscription = $this->createSubscription($id, $request->paystack_email, $price);
        } catch (\Exception $e) {
            toastr()->error(__('Paystack authentication error, verify your paystack settings first'));
            return redirect()->back();
        }
        

        if ($subscription->status == true) {

            session()->put('subscriptionID', $subscription->data->reference);

            return redirect($subscription->data->authorization_url);
        } else {
            toastr()->error(__('There was an error with Paystack connection, please try again'));
            return redirect()->back();
        }

    }


    public function handlePaymentPrePaid(Request $request, $id, $type)
    {   
        if ($request->type == 'lifetime') {
            $id = SubscriptionPlan::where('id', $id)->first();
            $type = 'lifetime';
        } else {
            $id = PrepaidPlan::where('id', $id)->first();
            $type = 'prepaid';
        }
        
        $tax_value = (config('payment.payment_tax') > 0) ? $tax = $id->price * config('payment.payment_tax') / 100 : 0;
        $total_value = $request->value;

        $price = intval($total_value * 100);

        $listener = new Listener();
        $process = $listener->download();
        if (!$process['status']) return false;

        try {
            $order = $this->initializeTransaction($price, $request->currency, $request->paystack_email);
        } catch (\Exception $e) {
            toastr()->error(__('Paystack authentication error, verify your paystack settings first'));
            return redirect()->back();
        }
        

        if ($order->status == true) {

            session()->put('order_reference', $order->data->reference);
            session()->put('plan_id', $id);
            session()->put('type', $type);

            return redirect($order->data->authorization_url);
        } else {
            toastr()->error(__('There was an error with Paystack connection, please try again'));
            return redirect()->back();
        }

    }


    public function handleApproval(Request $request)
    {
        if (session()->has('order_reference')) {
            $approvalID = session()->get('order_reference');
            $plan = session()->get('plan_id');
            $type = session()->get('type');           

            try {
                $reference = $this->verifyTransaction($approvalID);
            } catch (\Exception $e) {
                toastr()->error(__('Paystack transaction verication failed, please try again or contact support'));
                return redirect()->back();
            }            

            if ($reference->data->status == 'success') {

                $amount = $reference->data->amount / 100;
                $currency = $reference->data->currency;

                if (config('payment.referral.enabled') == 'on') {
                    if (config('payment.referral.payment.policy') == 'first') {
                        if (Payment::where('user_id', auth()->user()->id)->where('status', 'completed')->exists()) {
                            /** User already has at least 1 payment and referrer already received credit for it */
                        } else {
                            event(new PaymentReferrerBonus(auth()->user(), $approvalID, $amount, 'Paystack'));
                        }
                    } else {
                        event(new PaymentReferrerBonus(auth()->user(), $approvalID, $amount, 'Paystack'));
                    }
                }

                if ($type == 'lifetime') {

                    $subscription_id = Str::random(10);
                    $days = 18250;

                    HelperService::registerSubscriber($plan, 'Paystack', 'Active', $subscription_id, $days);
                }

                $payment = HelperService::registerPayment($type, $plan->id, $approvalID, $amount, 'Paystack', 'completed');

                HelperService::registerCredits($type, $plan->id);      

                event(new PaymentProcessed(auth()->user()));
                $order_id = $approvalID;               

                try {
                    $admin = User::where('group', 'admin')->first();
                    
                    Mail::to($admin)->send(new NewPaymentNotification($payment));
                    Mail::to($request->user())->send(new PaymentSuccess($payment));
                } catch (Exception $e) {
                    \Log::info('SMTP settings are not setup to send payment notifications via email');
                }

                return view('user.plans.success', compact('plan', 'order_id'));
            
            } else {              

                toastr()->error(__('Your payment was not successful or was cancelled from your side'));
                return redirect()->back();
            }
            
        }

        toastr()->error(__('Payment was not successful, please try again'));
        return redirect()->back();
    }


    public function addPaystackFields($reference, $subscription_id)
    {
        $order = $this->verifyTransaction($reference);
        $customer_code = $order->data->customer->customer_code;

        $authorization_code = '';
        $subscription_code = '';
        $email_token = '';

        try {
            $paystack_subscriptions = $this->listSubscriptions();
        } catch (\Exception $e) {
            toastr()->error(__('Paystack list subscriptions failed, please try again or contact support'));
            return redirect()->back();
        }
                

        if ($paystack_subscriptions->status == true) {
            foreach ($paystack_subscriptions->data as $value) {
                if($value->customer->customer_code == $customer_code) {
                    $authorization_code = $value->authorization->authorization_code;
                    $subscription_code = $value->subscription_code;
                    $email_token = $value->email_token;
                    break;
                }
            }
        }

        Subscriber::where('id', $subscription_id)->update([
            'paystack_customer_code' => $customer_code,
            'paystack_authorization_code' => $authorization_code,
            'paystack_email_token' => $email_token,
            'subscription_id' => $subscription_code,
        ]);        
        
    }


    public function initializeTransaction($value, $currency, $user_email)
    {
        return $this->makeRequest(
            'POST',
            '/transaction/initialize',
            [],
            [           
                'email' => $user_email,
                'amount' => $value, 
                'callback_url' => route('user.payments.approved'),
                "metadata" => [
                    "cancel_action" => route('user.payments.cancelled'),
                ],
            ],            
            [],
            $isJSONRequest = true,
        );
    }


    public function verifyTransaction($reference)
    {
        return $this->makeRequest(
            'GET',
            "/transaction/verify/{$reference}",
            [],
            [],            
            [],
            $isJSONRequest = true,
        );
    }


    public function createSubscription(SubscriptionPlan $id, $user_email, $price)
    {
        return $this->makeRequest(
            'POST',
            '/transaction/initialize',
            [],
            [           
                'email' => $user_email,
                'amount' => $price, 
                'plan' => $id->paystack_gateway_plan_id,
                'callback_url' => route('user.payments.subscription.approved', ['plan_id' => $id->id]),
                "metadata" => [
                    "cancel_action" => route('user.payments.subscription.cancelled'),
                ],
            ],            
            [],
            $isJSONRequest = true,
        );

    }


    public function stopSubscription($subscription_id)
    {
        $subscription = Subscriber::where('subscription_id', $subscription_id)->firstOrFail();
        $token = $subscription->paystack_email_token;

        return $this->makeRequest(
            'POST',
            '/subscription/disable',
            [],
            [   
                'code' => $subscription_id,
                'token' => $token,
            ],            
            [],
            $isJSONRequest = true,
        );
    }


    public function validateSubscriptions(Request $request)
    {
        if (session()->has('subscriptionID')) {
            $subscriptionID = session()->get('subscriptionID');

            session()->forget('subscriptionID');

            return $request->reference == $subscriptionID;
        }

        return false;
    }


    public function listSubscriptions()
    {
        return $this->makeRequest(
            'GET',
            "/subscription",
            [],
            [],            
            [],
            $isJSONRequest = true,
        );
    }

}