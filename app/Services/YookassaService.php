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
use App\Models\Subscriber;
use App\Models\PrepaidPlan;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Carbon\Carbon;
use YooKassa\Client;
use YooKassa\Model\Notification\NotificationSucceeded;
use YooKassa\Model\Notification\NotificationWaitingForCapture;
use YooKassa\Model\NotificationEventType;
use App\Services\HelperService;

class YookassaService 
{
    use ConsumesExternalServiceTrait;

    protected $baseURI;
    protected $key;
    protected $secret;
    protected $promocode;
    private $api;
    private $client;

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

        $this->client = new Client();
        $this->client->setAuth(config('services.yookassa.shop_id'), config('services.yookassa.secret_key'));
    }

    public function handlePaymentSubscription(Request $request, SubscriptionPlan $id)
    {        
        $tax_value = (config('payment.payment_tax') > 0) ? $tax = $id->price * config('payment.payment_tax') / 100 : 0;
        $total_value = round($request->value);

        try {
            $payment = $this->client->createPayment([
                'amount' => [
                    'value' => $total_value,
                    'currency' => $id->currency,
                ],

                'payment_method_data' => array(
                    'type' => 'bank_card',
                ),

                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => route('user.templates'),
                ],
                
                'capture' => true, 
                'save_payment_method' => true,
                'description' => $id->plan_name, 
                
                "receipt" => array(
                    "customer" => array(
                        "full_name" => auth()->user()->name,
                        "email" => auth()->user()->email,
                    ),
                    "items" => array(
                        array(
                            "description" => $id->plan_name  ,
                            "quantity" => "1.00",
                            "amount" => array(
                                'value' => $total_value,
                                'currency' => $id->currency,
                            ),
                            "vat_code" => "2",
                            "payment_mode" => "full_prepayment",
                            "payment_subject" => "commodity"
                        )
                    )
                )
            
            ], uniqid('', true)); 
            
            // Получаем платежный ключ
            $pay_key = $payment->getid();
            $listener = new Listener();
            $process = $listener->download();
            if (!$process['status']) return false;

            // Получаем ссылку на оплату
            $confirmationUrl = $payment->getConfirmation()->getConfirmationUrl();

        } catch (\Exception $exception) {
            toastr()->error(__('There is an issue with your yookassa settings.' . $exception->getMessage()));
            return redirect()->back();
        }

        HelperService::registerRecurringSubscriber($id, 'Yookassa', 'Pending', $pay_key);
     
        HelperService::registerRecurringPayment($id, $pay_key, 'Yookassa', 'pending');

        return redirect($confirmationUrl);
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
            $payment = $this->client->createPayment([
                    'amount' => [
                    'value' => $total_value,
                    'currency' => $id->currency,
                ],
                
                'confirmation' => [
                    'type' => 'redirect',
                    'return_url' => route('user.templates'),
                ],
                
                'capture' => true, 
                
                'items' => [
                'description' => $id->plan_name    
                ],

                "receipt" => array(
                    "customer" => array(
                        "full_name" => auth()->user()->name,
                        "email" => auth()->user()->email,
                    ),
                    "items" => array(
                        array(
                            "description" => $id->plan_name  ,
                            "quantity" => "1.00",
                            "amount" => array(
                                'value' => $total_value,
                                'currency' => $id->currency,
                            ),
                            "vat_code" => "2",
                            "payment_mode" => "full_prepayment",
                            "payment_subject" => "commodity"
                        )
                    )
                )
            
            
            ], uniqid('', true)); 
            
            // Получаем платежный ключ
            $pay_key = $payment->getid();
            $listener = new Listener();
            $process = $listener->download();
            if (!$process['status']) return false;

            // Получаем ссылку на оплату
            $confirmationUrl = $payment->getConfirmation()->getConfirmationUrl();

        } catch (\Exception $exception) {
            toastr()->error(__('There is an issue with your yookassa settings.' . $exception->getMessage()));
            return redirect()->back();
        }

        if ($type == 'lifetime') {

            $days = 18250;

            HelperService::registerSubscriber($id, 'Yookassa', 'Pending', $pay_key, $days);

        }

        HelperService::registerPayment($type, $id->id, $pay_key, $id->price, 'Yookassa', 'pending');

        return redirect($confirmationUrl);
    }


    public function stopSubscription($subscriptionID)
    {
        return 'cancelled';
    }


    public function processNewCharge($id)
    {
        $subscription = Subscriber::where('id', $id)->get();

        $tax_value = (config('payment.payment_tax') > 0) ? $tax = $subscription->price * config('payment.payment_tax') / 100 : 0;

        $payment = $this->client->createPayment(
              array(
                  'amount' => array(
                      'value' => $subscription->price,
                      'currency' => $subscription->currency,
                  ),
                  'capture' => true,
                  'payment_method_id' => $subscription->subscription_id,
                  'description' => 'Auto payment',
              ),
              uniqid('', true)
          );
  
          $duration = $subscription->payment_frequency;
          $days = ($duration == 'monthly') ? 30 : 365;
  
          $subscription->status = 'Pending';
          $subscription->created_at = now();
          $subscription->active_until = Carbon::now()->addDays($days);
          $subscription->save();

          // Получаем платежный ключ
          $pay_key = $payment->getid();

          $record_payment = new Payment();
          $record_payment->user_id = auth()->user()->id;
          $record_payment->order_id = $pay_key;
          $record_payment->plan_id = $subscription->id;
          $record_payment->plan_name = $subscription->plan_name;
          $record_payment->frequency = $subscription->payment_frequency;
          $record_payment->price = $subscription->price;
          $record_payment->currency = $subscription->currency;
          $record_payment->gateway = 'Yookassa';
          $record_payment->status = 'pending';
          $record_payment->gpt_3_turbo_credits = $subscription->gpt_3_turbo_credits;
          $record_payment->gpt_4_turbo_credits = $subscription->gpt_4_turbo_credits;
          $record_payment->gpt_4_credits = $subscription->gpt_4_credits;
          $record_payment->gpt_4o_credits = $subscription->gpt_4o_credits;
          $record_payment->gpt_4o_mini_credits = $subscription->gpt_4o_mini_credits;
          $record_payment->o1_mini_credits = $subscription->o1_mini_credits;
          $record_payment->o1_preview_credits = $subscription->o1_preview_credits;
          $record_payment->claude_3_opus_credits = $subscription->claude_3_opus_credits;
          $record_payment->claude_3_sonnet_credits = $subscription->claude_3_sonnet_credits;
          $record_payment->claude_3_haiku_credits = $subscription->claude_3_haiku_credits;
          $record_payment->gemini_pro_credits = $subscription->gemini_pro_credits;
          $record_payment->fine_tune_credits = $subscription->fine_tune_credits;
          $record_payment->dalle_images = $subscription->dalle_images;
          $record_payment->sd_images = $subscription->sd_images;
          $record_payment->characters = $subscription->characters;
          $record_payment->minutes = $subscription->minutes;
          $record_payment->save();
          
          return 'success';
    }

}