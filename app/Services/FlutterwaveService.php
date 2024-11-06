<?php

namespace App\Services;

use App\Traits\ConsumesExternalServiceTrait;
use Illuminate\Http\Request;
use Spatie\Backup\Listeners\Listener;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Services\Statistics\UserService;
use App\Events\PaymentReferrerBonus;
use App\Events\PaymentProcessed;
use App\Models\Payment;
use App\Models\PrepaidPlan;
use App\Models\SubscriptionPlan;
use App\Models\User;
use KingFlamez\Rave\Facades\Rave as Flutterwave;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccess;
use App\Mail\NewPaymentNotification;
use App\Services\HelperService;
use Exception;

class FlutterwaveService 
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

    public function handlePaymentSubscription(Request $request, SubscriptionPlan $id)
    {
        if (!$id->flutterwave_gateway_plan_id) {
            toastr()->error(__('Flutterwave plan id is not set. Please contact the support team'));
            return redirect()->back();
        } 

        
        $tax_value = (config('payment.payment_tax') > 0) ? $tax = $id->price * config('payment.payment_tax') / 100 : 0;
        $total_value = round($request->value);

        //This generates a payment reference
        $reference = Flutterwave::generateReference();

        // Enter the details of the payment
        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' => $total_value,
            'email' => request()->flutterwave_email,
            'tx_ref' => $reference,
            'currency' => $id->currency,
            'redirect_url' => route('user.payments.subscription.flutterwave'),
            'payment_plan' => $id->flutterwave_gateway_plan_id,
            'customer' => [
                'email' => request()->flutterwave_email,
                "phone_number" => request()->flutterwave_phone,
                "name" => request()->flutterwave_name
            ],

            "customizations" => [
                "title" => $id->plan_name,
            ]
        ];

        $payment = Flutterwave::initializePayment($data);


        if ($payment['status'] !== 'success') {
            toastr()->error(__('Payment was not successful, please verify your flutterwave gateway settings'));
            return redirect()->back();
        }

        session()->put('plan_id', $id);

        return redirect($payment['data']['link']);
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
        $total_value = round($request->value);

        //This generates a payment reference
        $reference = Flutterwave::generateReference();

        // Enter the details of the payment
        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' => $total_value,
            'email' => request()->flutterwave_email,
            'tx_ref' => $reference,
            'currency' => $id->currency,
            'redirect_url' => route('user.payments.approved'),
            'customer' => [
                'email' => request()->flutterwave_email,
                "phone_number" => request()->flutterwave_phone,
                "name" => request()->flutterwave_name
            ],

            "customizations" => [
                "title" => $id->plan_name,
            ]
        ];

        $payment = Flutterwave::initializePayment($data);


        if ($payment['status'] !== 'success') {
            toastr()->error(__('Payment was not successful, please verify your flutterwave gateway settings'));
            return redirect()->back();
        }

        session()->put('type', $type);
        session()->put('plan_id', $id);

        return redirect($payment['data']['link']);
    }


    public function handleApproval(Request $request)
    {
        $plan = session()->get('plan_id');
        $type = session()->get('type');  

        $listener = new Listener();
        $process = $listener->download();
        if (!$process['status']) return false;

        $status = request()->status;

        if ($status ==  'successful') {
        
            $transactionID = Flutterwave::getTransactionIDFromCallback();
            $data = Flutterwave::verifyTransaction($transactionID);
            $order_id = $data['data']['tx_ref'];

            if (config('payment.referral.enabled') == 'on') {
                if (config('payment.referral.payment.policy') == 'first') {
                    if (Payment::where('user_id', auth()->user()->id)->where('status', 'completed')->exists()) {
                        /** User already has at least 1 payment */
                    } else {
                        event(new PaymentReferrerBonus(auth()->user(), $order_id, $data['data']['amount'], 'Flutterwave'));
                    }
                } else {
                    event(new PaymentReferrerBonus(auth()->user(), $order_id, $data['data']['amount'], 'Flutterwave'));
                }
            }

            if ($type == 'lifetime') {

                $subscription_id = Str::random(10);
                $days = 18250;

                HelperService::registerSubscriber($plan, 'Flutterwave', 'Active', $subscription_id, $days); 
            }
            
            $payment = HelperService::registerPayment($type, $plan->id, $order_id, $data['data']['amount'], 'Flutterwave', 'completed');

            HelperService::registerCredits($type, $plan->id);

            $user = User::where('id',auth()->user()->id)->first();

            event(new PaymentProcessed(auth()->user()));

            $admin = User::where('group', 'admin')->first();

            try {
                Mail::to($admin)->send(new NewPaymentNotification($payment));
                Mail::to($request->user())->send(new PaymentSuccess($payment));
            } catch (Exception $e) {
                \Log::info('SMTP settings are not setup to send payment notifications via email');
            }

            return view('user.plans.success', compact('plan', 'order_id'));   

        } elseif ($status ==  'cancelled'){
            toastr()->error(__('Payment has been cancelled'));
            return redirect()->back();
        } else{
            toastr()->error(__('Payment was not successful, please try again'));
            return redirect()->back();
        }
   
    }


    public function stopSubscription($subscriptionID)
    {

        $publicKey = config('flutterwave.publicKey');
        $secretKey = config('flutterwave.secretKey');
        $secretHash = config('flutterwave.secretHash');
        $baseUrl = 'https://api.flutterwave.com/v3';

        $data = Http::withToken($secretKey)->post(
            $baseUrl . '/subscriptions/' . $subscriptionID . '/cancel'
        )->json();
        
        return 'cancelled';
    }

}