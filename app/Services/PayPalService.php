<?php

namespace App\Services;

use App\Traits\ConsumesExternalServiceTrait;
use App\Events\PaymentReferrerBonus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Events\PaymentProcessed;
use App\Models\Payment;
use App\Models\PrepaidPlan;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccess;
use App\Mail\NewPaymentNotification;
use App\Services\HelperService;
use Exception;

class PayPalService 
{
    use ConsumesExternalServiceTrait;

    protected $baseURI;
    protected $clientID;
    protected $clientSecret;
    private $api;

    /**
     * Paypal payment processing, unless you are familiar with 
     * Paypal's REST API, we recommend not to modify core payment processing functionalities here.
     * Part that are writing data to the database can be edited as you prefer.
     */
    public function __construct()
    {


        $this->baseURI = config('services.paypal.base_uri');
        $this->clientID = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
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
        
        $credentials = base64_encode("{$this->clientID}:{$this->clientSecret}");

        return "Basic {$credentials}";
    }


    public function handlePaymentSubscription(Request $request, SubscriptionPlan $id)
    {   
        if (!$id->paypal_gateway_plan_id) {
            toastr()->error(__('Paypal plan id is not set. Please contact the support team'));
            return redirect()->back();
        }
        
        try {
            $subscription = $this->createSubscription($id, $request->user()->name, $request->user()->email);
        } catch (\Exception $e) {
            toastr()->error(__('Paypal authentication error, verify your paypal settings first'));
            return redirect()->back();
        }        

        $subscriptionLinks = collect($subscription->links);

        $approve = $subscriptionLinks->where('rel', 'approve')->first();

        session()->put('subscriptionID', $subscription->id);

        return redirect($approve->href);
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

        try {
            $order = $this->createOrder($total_value, $request->currency);
         
        } catch (\Exception $e) {
            toastr()->error(__('Paypal authentication error, verify your paypal settings first2'));
            return redirect()->back();
        }
        

        $orderLinks = collect($order->links);

        $approve = $orderLinks->where('rel', 'approve')->first();

        session()->put('approvalID', $order->id);
        session()->put('plan_id', $id);
        session()->put('type', $type);

        return redirect($approve->href);
    }


    public function handleApproval(Request $request)
    {
        if (session()->has('approvalID')) {
            $approvalID = session()->get('approvalID');
            $plan = session()->get('plan_id');        
            $type = session()->get('type');        
           
            try {
                $payment = $this->capturePayment($approvalID);
            } catch (\Exception $e) {
                toastr()->error(__('Paypal payment capture error. Verify your paypal merchant account settings'));
                return redirect()->back();
            }
            

            $name = $payment->payer->name->given_name;
            $payment = $payment->purchase_units[0]->payments->captures[0]->amount;
            $amount = $payment->value;
            $currency = $payment->currency_code;

            if ($approvalID != 1) {
                if (config('payment.referral.enabled') == 'on') {
                    if (config('payment.referral.payment.policy') == 'first') {
                        if (Payment::where('user_id', auth()->user()->id)->where('status', 'completed')->exists()) {
                            /** User already has at least 1 payment and referrer already received credit for it */
                        } else {
                            event(new PaymentReferrerBonus(auth()->user(), $approvalID, $amount, 'PayPal'));
                        }
                    } else {
                        event(new PaymentReferrerBonus(auth()->user(), $approvalID, $amount, 'PayPal'));
                    }
                }
            }
            

            if ($type == 'lifetime') {

                $subscription_id = Str::random(10);
                $days = 18250;

                HelperService::registerSubscriber($plan, 'PayPal', 'Active', $subscription_id, $days);
            }

            $payment = HelperService::registerPayment($type, $plan->id, $approvalID, $amount, 'PayPal', 'completed');

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
        }

        toastr()->error(__('Payment was not successful, please try again'));
        return redirect()->back();
    }


    public function createOrder($value, $currency)
    {

        return $this->makeRequest(
            'POST',
            '/v2/checkout/orders',
            [],
            [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    0 => [
                        'amount' => [
                            'currency_code' => strtoupper($currency),
                            'value' => number_format($value, 2, '.', ''),
                        ]
                    ]   
                ],   
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW',
                    'return_url' => route('user.payments.approved'),
                    'cancel_url' => route('user.payments.cancelled'),
                ]
            ],            
            [],
            $isJSONRequest = true,
        );
    }


    public function capturePayment($approvalID)
    {
        return $this->makeRequest(
            'POST',
            "/v2/checkout/orders/{$approvalID}/capture",
            [],
            [],
            [
                'Content-Type' => 'application/json'
            ]
        );
    }    


    public function resolveFactor($currency)
    {
        $zeroDecimanCurrency = ['JPY'];

        if (in_array(strtoupper($currency), $zeroDecimanCurrency)) {
            return 1;
        }

        return 100;
    }


    public function createSubscription(SubscriptionPlan $id, $name, $email)
    {
        return $this->makeRequest(
            'POST',
            '/v1/billing/subscriptions',
            [],
            [   
                'plan_id' => $id->paypal_gateway_plan_id,
                'subscriber' => [
                    'name' => [
                        'given_name' => $name,
                    ],
                    'email_address' => $email,
                ],   
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'SUBSCRIBE_NOW',
                    'return_url' => route('user.payments.subscription.approved', ['plan_id' => $id->id]),
                    'cancel_url' => route('user.payments.subscription.cancelled'),
                ]
            ],            
            [],
            $isJSONRequest = true,
        );
    }


    public function stopSubscription($subscriptionID)
    {
        return $this->makeRequest(
            'POST',
            '/v1/billing/subscriptions/' . $subscriptionID . '/cancel',
            [],
            [   
                'reason' => 'Just want to unsubscribe'
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

            return $request->subscription_id == $subscriptionID;
        }

        return false;
    }

}