<?php

namespace App\Http\Controllers\Admin\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\PaymentProcessed;
use App\Events\PaymentReferrerBonus;
use App\Models\Subscriber;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;

class RazorpayWebhookController extends Controller
{
    /**
     * Stripe Webhook processing, unless you are familiar with 
     * Stripe's PHP API, we recommend not to modify it
     */
    public function handleRazorpay(Request $request)
    {
        $input = file_get_contents('php://input');
        $webhook_signature = $request->header('x-razorpay-signature');
        
        $body = json_decode($input, true);            

        $generated_signature = hash_hmac('sha256', $input, config('services.razorpay.webhook_secret'));

        if($generated_signature != $webhook_signature ) {
            exit();
        }

        switch ($body->event) {
            case 'subscription.cancelled': 
                $subscription = Subscriber::where('subscription_id', $body->payload->subscription->id)->firstOrFail();
                $subscription->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())->endOfMonth()]);
                
                $user = User::where('id', $subscription->user_id)->firstOrFail();
                $group = ($user->hasRole('admin')) ? 'admin' : 'user';
                if ($group == 'user') {
                    $user->syncRoles($group);    
                    $user->group = $group;
                    $user->plan_id = null;
                    $user->member_limit = null;
                    $user->save();
                } else {
                    $user->syncRoles($group);    
                    $user->group = $group;
                    $user->plan_id = null;
                    $user->save();
                }
                
                break;
            case 'subscription.charged':
                $subscription = Subscriber::where('paystack_customer_code', $body->payload->subscription->id)->where('status', 'Expired')->firstOrFail();

                if ($subscription) {
                    $plan = SubscriptionPlan::where('id', $subscription->plan_id)->firstOrFail();
                    $duration = ($plan->payment_frequency == 'monthly') ? 30 : 365;

                    $subscription->update([
                        'status' => 'Active', 
                        'active_until' => Carbon::now()->addDays($duration)
                    ]);
                    
                    $user = User::where('id', $subscription->user_id)->firstOrFail();

                    $tax_value = (config('payment.payment_tax') > 0) ? $plan->price * config('payment.payment_tax') / 100 : 0;
                    $total_price = $tax_value + $plan->price;

                    if (config('payment.referral.enabled') == 'on') {
                        if (config('payment.referral.payment.policy') == 'first') {
                            if (Payment::where('user_id', $user->id)->where('status', 'completed')->exists()) {
                                /** User already has at least 1 payment */
                            } else {
                                event(new PaymentReferrerBonus($user, $subscription->plan_id, $total_price, 'Razorpay'));
                            }
                        } else {
                            event(new PaymentReferrerBonus($user, $subscription->plan_id, $total_price, 'Razorpay'));
                        }
                    }

                    $record_payment = new Payment();
                    $record_payment->user_id = $user->id;
                    $record_payment->plan_id = $plan->id;
                    $record_payment->order_id = $subscription->plan_id;
                    $record_payment->plan_name = $plan->plan_name;
                    $record_payment->price = $total_price;
                    $record_payment->currency = $plan->currency;
                    $record_payment->gateway = 'Razorpay';
                    $record_payment->frequency = $plan->payment_frequency;
                    $record_payment->status = 'completed';
                    $record_payment->gpt_3_turbo_credits = $plan->gpt_3_turbo_credits;
                    $record_payment->gpt_4_turbo_credits = $plan->gpt_4_turbo_credits;
                    $record_payment->gpt_4_credits = $plan->gpt_4_credits;
                    $record_payment->gpt_4o_credits = $plan->gpt_4o_credits;
                    $record_payment->gpt_4o_mini_credits = $plan->gpt_4o_mini_credits;
                    $record_payment->claude_3_opus_credits = $plan->claude_3_opus_credits;
                    $record_payment->claude_3_sonnet_credits = $plan->claude_3_sonnet_credits;
                    $record_payment->claude_3_haiku_credits = $plan->claude_3_haiku_credits;
                    $record_payment->gemini_pro_credits = $plan->gemini_pro_credits;
                    $record_payment->fine_tune_credits = $plan->fine_tune_credits;
                    $record_payment->dalle_images = $plan->dalle_images;
                    $record_payment->sd_images = $plan->sd_images;
                    $record_payment->save();
                    
                    $group = ($user->hasRole('admin')) ? 'admin' : 'subscriber';

                    $user->syncRoles($group);    
                    $user->group = $group;
                    $user->plan_id = $plan->id;
                    $user->gpt_3_turbo_credits = $plan->gpt_3_turbo_credits;
                    $user->gpt_4_turbo_credits = $plan->gpt_4_turbo_credits;
                    $user->gpt_4_credits = $plan->gpt_4_credits;
                    $user->gpt_4o_credits = $plan->gpt_4o_credits;
                    $user->gpt_4o_mini_credits = $plan->gpt_4o_mini_credits;
                    $user->claude_3_opus_credits = $plan->claude_3_opus_credits;
                    $user->claude_3_sonnet_credits = $plan->claude_3_sonnet_credits;
                    $user->claude_3_haiku_credits = $plan->claude_3_haiku_credits;
                    $user->gemini_pro_credits = $plan->gemini_pro_credits;
                    $user->fine_tune_credits = $plan->fine_tune_credits;
                    $user->available_chars = $plan->characters;
                    $user->available_minutes = $plan->minutes;
                    $user->member_limit = $plan->team_members;
                    $user->available_dalle_images = $plan->dalle_images;
                    $user->available_sd_images = $plan->sd_images;
                    $user->save();       

                    event(new PaymentProcessed($user));
                }                
          
                break;
        }    
    }
}
