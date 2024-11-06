<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\Statistics\UserService;
use App\Events\PaymentReferrerBonus;
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

class BraintreeService 
{

    protected $gateway;
    protected $promocode;
    private $api;


    public function __construct()
    {
        $this->api = new UserService();

        $verify = $this->api->verify_license();

        if($verify['status']!=true){
            return false;
        }

        try {
            $this->gateway = new \Braintree\Gateway([
                'environment' => config('services.braintree.env'),
                'merchantId' => config('services.braintree.merchant_id'),
                'publicKey' => config('services.braintree.public_key'),
                'privateKey' => config('services.braintree.private_key')
            ]);
        } catch (\Exception $e) {
            toastr()->error(__('Braintree authentication error, verify your braintree settings first'));
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

        try {

            $clientToken = $this->gateway->clientToken()->generate();
            
            session()->put('type', $type);
            session()->put('plan_id', $id);
            session()->put('total_amount', $total_value);

        } catch (\Exception $e) {
            toastr()->error(__('Braintree authentication error, verify your braintree settings first'));
            return redirect()->back();
        }

        return view('user.plans.braintree-checkout', compact('id', 'clientToken'));

    }


    public function handleApproval(Request $request)
    {        
        $payload = $request->input('payload', false);
        $nonce = $payload['nonce'];
        $total_amount = session()->get('total_amount');

        try {
            $result = $this->gateway->transaction()->sale([
                'amount' => $total_amount,
                'paymentMethodNonce' => $nonce,
                'options' => [
                    'submitForSettlement' => True
                ]
            ]);
        } catch (\Exception $e) {
            toastr()->error(__('Braintree transaction error, verify your braintree settings first'));
            return redirect()->back();
        }        
        
        $plan = session()->get('plan_id');
        $type = session()->get('type');  

        if ($result->success) {

            if (config('payment.referral.enabled') == 'on') {
                if (config('payment.referral.payment.policy') == 'first') {
                    if (Payment::where('user_id', auth()->user()->id)->where('status', 'completed')->exists()) {
                        /** User already has at least 1 payment */
                    } else {
                        event(new PaymentReferrerBonus(auth()->user(), $result->transaction->id, $result->transaction->amount, 'Braintree'));
                    }
                } else {
                    event(new PaymentReferrerBonus(auth()->user(), $result->transaction->id, $result->transaction->amount, 'Braintree'));
                }
            }

            if ($type == 'lifetime') {

                $subscription_id = Str::random(10);
                $days = 18250;

                HelperService::registerSubscriber($plan, 'Braintree', 'Active', $subscription_id, $days);

            }

            $payment = HelperService::registerPayment($type, $plan->id, $result->transaction->id, $result->transaction->amount, 'Braintree', 'completed');

            HelperService::registerCredits($type, $plan->id);


            event(new PaymentProcessed(auth()->user()));

            $data['result'] = $result;
            $data['plan'] = $plan->id;
            $data['order_id'] = $result->transaction->id;

            $admin = User::where('group', 'admin')->first();

            try {
                Mail::to($admin)->send(new NewPaymentNotification($payment));
                Mail::to($request->user())->send(new PaymentSuccess($payment));
            } catch (Exception $e) {
                \Log::info('SMTP settings are not setup to send payment notifications via email');
            }

            return response()->json($data);

        } else {
            toastr()->error(__('Payment was not successful, please try again'));
            return redirect()->back();
        }

                
    }

}