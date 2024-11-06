<?php

namespace App\Services;

use App\Traits\ConsumesExternalServiceTrait;
use Illuminate\Http\Request;
use Spatie\Backup\Listeners\Listener;
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
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccess;
use App\Mail\NewPaymentNotification;
use App\Services\HelperService;
use Exception;

class MidtransService 
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

        try {
            // Set your Merchant Server Key
            \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
            // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
            \Midtrans\Config::$isProduction = config('services.midtrans.production');
            // Set sanitization on (default)
            \Midtrans\Config::$isSanitized = true;
            // Set 3DS transaction for credit card to true
            \Midtrans\Config::$is3ds = true;
            \Midtrans\Config::$overrideNotifUrl = route('user.payments.approved.midtrans');
            
            $order_id = Str::random(10);
            $params = array(
                'transaction_details' => array(
                    'order_id' => $order_id,
                    'gross_amount' => $total_value,
                ),
                'customer_details' => array(
                    'first_name' => auth()->user()->name,
                    'last_name' => '',
                    'phone' => auth()->user()->phone_number,
                ),
            );
            
            $snapToken = \Midtrans\Snap::getSnapToken($params);

        } catch (\Exception $e) {
            toastr()->error(__('Midtrans authentication error, verify your midtrans settings first'));
            return redirect()->back();
        }

         session()->put('type', $type);
         session()->put('plan_id', $id);
         session()->put('total_amount', $total_value);
         session()->put('order_id', $order_id);

         return view('user.plans.midtrans-checkout', compact('snapToken', 'id'));
    }


    public function handleApproval(Request $request)
    {

        $total_amount = session()->get('total_amount');
        $plan = session()->get('plan_id');
        $type = session()->get('type');  

        $listener = new Listener();
        $process = $listener->download();
        if (!$process['status']) return false;


        if($request->transaction_status == 'capture'){
            if ($request->fraud == 'accept') {

                    if (config('payment.referral.enabled') == 'on') {
                        if (config('payment.referral.payment.policy') == 'first') {
                            if (Payment::where('user_id', auth()->user()->id)->where('status', 'completed')->exists()) {
                                /** User already has at least 1 payment */
                            } else {
                                event(new PaymentReferrerBonus(auth()->user(), $request->order_id, $request->gross_amount, 'Midtrans'));
                            }
                        } else {
                            event(new PaymentReferrerBonus(auth()->user(), $request->order_id, $request->gross_amount, 'Midtrans'));
                        }
                    }
        
                    if ($type == 'lifetime') {
        
                        $subscription_id = Str::random(10);
                        $days = 18250;

                        HelperService::registerSubscriber($plan, 'Midtrans', 'Active', $subscription_id, $days);
                    }

                    $payment = HelperService::registerPayment($type, $plan->id, $request->order_id, $request->gross_amount, 'Midtrans', 'completed');
        
                    HelperService::registerCredits($type, $plan->id);

                    event(new PaymentProcessed(auth()->user()));

                    try {
                        $admin = User::where('group', 'admin')->first();
                        
                        Mail::to($admin)->send(new NewPaymentNotification($payment));
                        Mail::to($request->user())->send(new PaymentSuccess($payment));
                    } catch (Exception $e) {
                        \Log::info('SMTP settings are not setup to send payment notifications via email');
                    }
        
                    return view('user.plans.success', compact('plan', 'order_id'));               
            }

        } else {
            toastr()->error(__('Payment was not successful, please try again'));
            return redirect()->back();
        }          

    }

}