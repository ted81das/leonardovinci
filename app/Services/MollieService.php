<?php

namespace App\Services;

use App\Traits\ConsumesExternalServiceTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Backup\Listeners\Listener;
use App\Services\Statistics\UserService;
use App\Events\PaymentReferrerBonus;
use App\Events\PaymentProcessed;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use App\Models\PrepaidPlan;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccess;
use App\Mail\NewPaymentNotification;
use App\Services\HelperService;
use Exception;

class MollieService 
{
    use ConsumesExternalServiceTrait;

    protected $mollie;
    protected $key;
    protected $baseURI;
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

        $this->key = config('services.mollie.key_id');
        $this->baseURI = config('services.mollie.base_uri');  
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
        
        return "Bearer {$this->key}"; 
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function handlePaymentSubscription(Request $request, SubscriptionPlan $id)
    {   
        try {
            $customer = $this->createCustomer($request->mollie_name, $request->mollie_email);
        } catch (\Exception $e) {
            toastr()->error(__('Mollie authentication error, verify your mollie settings first'));
            return redirect()->back();
        }        

        try {
            $payment = $this->createFirstPayment($customer->id, $id);                      
        } catch (\Exception $e) {
            toastr()->error(__('Mollie authentication error, verify your mollie settings first'));
            return redirect()->back();
        }        

        if ($payment->status == 'open') {
            session()->put('subscriptionID', $payment->id);
            session()->put('customerID', $payment->customerId);

            return redirect($payment->_links->checkout->href, 303);
        }        

        toastr()->error(__('There was an error while processing your payment. Please try again'));
        return redirect()->route('user.plans')->with('error', 'There was an error while processing your payment. Please try again');
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
            $payment = $this->createPayment($total_value, $request->currency);
        } catch (\Exception $e) {
            toastr()->error(__('Mollie authentication error, verify your mollie settings first'));
            return redirect()->back();
        }    
        
        $listener = new Listener();
        $process = $listener->download();
        if (!$process['status']) return false;

        
        if ($payment->status == 'open') {

            session()->put('paymentIntentID', $payment->id);
            session()->put('plan_id', $id);
            session()->put('type', $type);

            return redirect($payment->_links->checkout->href, 303);

        } else {
            toastr()->error(__('There was an error with Mollie payment, please try again'));
            return redirect()->back();
        }

    }


    public function handleApproval(Request $request)
    {        
        if (session()->has('paymentIntentID')) {
            $paymentIntentID = session()->get('paymentIntentID');
            $plan = session()->get('plan_id');
            $type = session()->get('type'); 

            try {
                $payment = $this->getPayment($paymentIntentID);
            } catch (\Exception $e) {
                toastr()->error(__('Mollie payment confirmation error. Please notify support team or try again'));
                return redirect()->back();
            }

            if ($payment->status == 'paid') {
                $amount = $payment->amount->value;
                $currency = strtoupper($payment->amount->currency);
            }

            if (config('payment.referral.enabled') == 'on') {
                if (config('payment.referral.payment.policy') == 'first') {
                    if (Payment::where('user_id', auth()->user()->id)->where('status', 'completed')->exists()) {
                        /** User already has at least 1 payment */
                    } else {
                        event(new PaymentReferrerBonus(auth()->user(), $paymentIntentID, $amount, 'Mollie'));
                    }
                } else {
                    event(new PaymentReferrerBonus(auth()->user(), $paymentIntentID, $amount, 'Mollie'));
                }
            }

            if ($type == 'lifetime') {

                $subscription_id = Str::random(10);
                $days = 18250;

                HelperService::registerSubscriber($plan, 'Mollie', 'Active', $subscription_id, $days);
            }

            $payment = HelperService::registerPayment($type, $plan->id, $paymentIntentID, $amount, 'Mollie', 'completed');

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

        return redirect()->back()->with('error', 'Payment was not successful, please try again');
    }


    public function addMollieFields(SubscriptionPlan $id, $subscription_id)
    {
        if (session()->has('customerID')) {
            
            $customerID = session()->get('customerID');
            session()->forget('customerID');

            $subscription = $this->createSubscription($customerID, $id);
          
            Subscriber::where('id', $subscription_id)->update([
                'subscription_id' => $subscription->id,
                'mollie_customer_id' => $customerID,
            ]);   
        
        }     
        
    }


    public function createPayment($value, $currency)
    {  
        return $this->makeRequest(
            'POST',
            '/v2/payments',
            [],
            [
                'amount'=>[
                    'currency' => $currency,
                    'value' => '' . sprintf('%0.2f', $value) . '',
                ],
                "description" => "Subscription Plan Payment",
                "redirectUrl" => route('user.payments.approved'),
                "webhookUrl"  => config('services.mollie.webhook_uri'),
            ]
        );        
    }


    public function getPayment($paymentID)
    {
        return $this->makeRequest(
            'GET',
            '/v2/payments/' . $paymentID,
            [],
            []
        );        
    }


    public function createCustomer($name, $email)
    {
        return $this->makeRequest(
            'POST',
            '/v2/customers',
            [],
            [
                'name' => $name,
                'email' => $email,
            ],
        );
    }


    public function createFirstPayment($customerID, SubscriptionPlan $id)
    {
        return $this->makeRequest(
            'POST',
            '/v2/payments',
            [],
            [
                'amount'=>[
                    'currency' => $id->currency,
                    'value' => $id->price,
                ],
                "customerId" => $customerID,
                "sequenceType" => "first",
                "description" => "Subscription Payment Mandate",
                "redirectUrl" => route('user.payments.subscription.approved', ['plan_id' => $id->id]),
                "webhookUrl"  => config('services.mollie.webhook_uri'),
            ],
            [],
            $isJSONRequest = true,
        );
    }


    public function createSubscription($customerID, SubscriptionPlan $id)
    {
        return $this->makeRequest(
            'POST',
            '/v2/customers/'. $customerID .'/subscriptions',
            [],
            [
                'amount'=>[
                    'currency' => $id->currency,
                    'value' => '' . sprintf('%0.2f', $id->price) . '',
                ],
                "interval" => "1 month",
                "description" => "Subscription Plan",
                "webhookUrl"  => config('services.mollie.webhook_uri'),
            ],
            [],
            $isJSONRequest = true,
        );
    }


    public function checkMandate($customerID)
    {
        return $this->makeRequest(
            'GET',
            '/v2/customers/' . $customerID . '/mandates',
            [],
            []
        );        
    }


    public function stopSubscription($subscriptionID)
    {
        $subscription = Subscriber::where('subscription_id', $subscriptionID)->firstOrFail();

        return $this->makeRequest(
            "DELETE",
            "/v2/customers/" . $subscription->mollie_customer_id . "/subscriptions/" . $subscription->subscription_id,
            [],
            [],
        );
    }


    public function validateSubscriptions(Request $request)
    {
        if (session()->has('customerID')) {

            $customerID = session()->get('customerID');
            session()->forget('subscriptionID');            

            $mandate = $this->checkMandate($customerID);

            if ($mandate->count == 1) {
                if ($mandate->_embedded->mandates[0]->status == 'valid' || $mandate->_embedded->mandates[0]->status == 'pending') {
                    return true;
                } else {
                    return false;
                }
            } elseif ($mandate->count > 1) {
                foreach ($mandate->_embedded->mandates as $value) {
                    if ($value->status == 'valid' || $value->status == 'pending') {
                        return true;
                        break;
                    }
                }
                return false;
            } else {
                return false;
            }
        }

        return false;
    }

}