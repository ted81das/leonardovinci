<?php

namespace App\Http\Controllers\Admin\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Events\PaymentProcessed;
use App\Events\PaymentReferrerBonus;
use App\Models\SubscriptionPlan;
use App\Models\PrepaidPlan;
use App\Models\Subscriber;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccess;
use App\Mail\NewPaymentNotification;
use App\Services\HelperService;
use Exception;

class CoinbaseWebhookController extends Controller
{
    /**
     * Stripe Webhook processing, unless you are familiar with 
     * Stripe's PHP API, we recommend not to modify it
     */
    public function handleCoinbase(Request $request)
    {
        $payload = json_decode($request->getContent());

        $computedSignature = hash_hmac('sha256', $request->getContent(), config('services.coinbase.webhook_secret'));

        if (hash_equals($computedSignature, $request->server('HTTP_X_CC_WEBHOOK_SIGNATURE'))) {

            $metadata = $payload->event->data->metadata ?? null;

            if (isset($metadata->user)) {

                $user = User::where('id', $metadata->user)->first();

                if ($user) {

                    if ($payload->event->type == 'charge:confirmed' || $payload->event->type == 'charge:resolved') {
                        
                        $payment = Payment::where('order_id', $payload->event->data->code)->first();
                        if ($metadata->type == 'lifetime') {
                            $plan = SubscriptionPlan::where('id', $metadata->plan_id)->first();
                        } else {
                            $plan = PrepaidPlan::where('id', $metadata->plan_id)->first();
                        }
                       

                        if (config('payment.referral.enabled') == 'on') {
                            if (config('payment.referral.payment.policy') == 'first') {
                                if (Payment::where('user_id', $user->id)->where('status', 'completed')->exists()) {
                                    /** User already has at least 1 payment */
                                } else {
                                    event(new PaymentReferrerBonus($user, $payload->event->data->code, $plan->price, 'Coinbase'));
                                }
                            } else {
                                event(new PaymentReferrerBonus($user, $payload->event->data->code, $plan->price, 'Coinbase'));
                            }
                        }

                        if ($payment) {

                            $payment->status = 'completed';
                            $payment->save();

                            $subscriber = Subscriber::where('subscription_id', $payload->event->data->code)->first();
                            $subscriber->status = 'Active';
                            $subscriber->save();


                            if ($metadata->type == 'lifetime') {
                                $group = ($user->hasRole('admin'))? 'admin' : 'subscriber';
                                $user->syncRoles($group);    
                                $user->group = $group;
                                $user->plan_id = $plan->id;
                                $user->available_dalle_images = $plan->dalle_images;
                                $user->available_sd_images = $plan->sd_images;
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
                            } else {
                                $user->gpt_3_turbo_credits_prepaid = ($user->gpt_3_turbo_credits_prepaid + $plan->gpt_3_turbo_credits_prepaid);
                                $user->gpt_4_turbo_credits_prepaid = ($user->gpt_4_turbo_credits_prepaid + $plan->gpt_4_turbo_credits_prepaid);
                                $user->gpt_4_credits_prepaid = ($user->gpt_4_credits_prepaid + $plan->gpt_4_credits_prepaid);
                                $user->gpt_4o_credits_prepaid = ($user->gpt_4o_credits_prepaid + $plan->gpt_4o_credits_prepaid);
                                $user->gpt_4o_mini_credits_prepaid = ($user->gpt_4o_mini_credits_prepaid + $plan->gpt_4o_mini_credits_prepaid);
                                $user->fine_tune_credits_prepaid = ($user->fine_tune_credits_prepaid + $plan->fine_tune_credits_prepaid);
                                $user->claude_3_opus_credits_prepaid = ($user->claude_3_opus_credits_prepaid + $plan->claude_3_opus_credits_prepaid);
                                $user->claude_3_sonnet_credits_prepaid = ($user->claude_3_sonnet_credits_prepaid + $plan->claude_3_sonnet_credits_prepaid);
                                $user->claude_3_haiku_credits_prepaid = ($user->claude_3_haiku_credits_prepaid + $plan->claude_3_haiku_credits_prepaid);
                                $user->gemini_pro_credits_prepaid = ($user->gemini_pro_credits_prepaid + $plan->gemini_pro_credits_prepaid);
                                $user->available_dalle_images_prepaid = $user->available_dalle_images_prepaid + $plan->dalle_images;
                                $user->available_sd_images_prepaid = $user->available_sd_images_prepaid + $plan->sd_images;
                                $user->available_chars_prepaid = $user->available_chars_prepaid + $plan->characters;
                                $user->available_minutes_prepaid = $user->available_minutes_prepaid + $plan->minutes;
                            }

                            $user->save();

                            event(new PaymentProcessed($user));

                            try {
                                $admin = User::where('group', 'admin')->first();
                                
                                Mail::to($admin)->send(new NewPaymentNotification($payment));
                                Mail::to($user)->send(new PaymentSuccess($payment));
                            } catch (Exception $e) {
                                \Log::info('SMTP settings are not setup to send payment notifications via email');
                            }

                        }                                       
                    }
                }
            }

        } else {

            Log::info('Coinbase signature validation failed.');

            return response()->json(['status' => 400], 400);
        }

        return response()->json(['status' => 200], 200);
    }
    
}
