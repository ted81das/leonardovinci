<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use App\Services\Statistics\DavinciUsageService;
use App\Models\SubscriptionPlan;
use App\Models\Subscriber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AddUser;
use App\Mail\AddCredits;
use Exception;


class UserManagementController extends Controller
{
    
    /**
     * List all users
     *
     * @OA\Get(
     *      path="/api/v1/admin/users/list",
     *      operationId="userList",
     *      tags={"User Management"},
     *      summary="User Management features for Admin Group",
     *      description="Admin group's user management API call for various tasks including assinging credits and subscriptions manually",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="users_per_page",
     *          in="path",
     *          description="Pagination for the returned users list",
     *          required=true,
     *          @OA\Schema(type="integer", example="10"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Ticket Not Found",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
    public function listUsers(Request $request)
    {  
        $perPage = $request->input('users_per_page', 10);

        $user = Auth::user();
        if ($user->group == 'admin') {
            $data = User::latest()->paginate($perPage);
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => __('Unauthorized: No permission')], 403);
        }

    }


    /**
     * Show a user
     *
     * @OA\Get(
     *      path="/api/v1/admin/users/{user_id}",
     *      operationId="userShow",
     *      tags={"User Management"},
     *      summary="Show user details",
     *      description="Return full user details, request is valid only for admin groups",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          description="ID of the user",
     *          required=true,
     *          @OA\Schema(type="integer", example="7"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Ticket Not Found",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
    public function showUser(Request $request, Int $user_id)
    {   
        if($user_id == null) return response()->json(['error' => __('User ID value missing.')], 412);

        $user = Auth::user();
        if ($user->group == 'admin') {
            $data = User::where('id', $user_id)->first();
            return response()->json($data, 200);
        } else {
            return response()->json(['error' => __('Unauthorized: No permission')], 403);
        }
    }


    /**
     * Create a user
     *
     * @OA\Post(
     *      path="/api/v1/admin/user/create",
     *      operationId="userCreate",
     *      tags={"User Management"},
     *      summary="Create a new user",
     *      description="Create a new user manually by admin",
     *      security={{ "passport": {} }},
     *      @OA\RequestBody(
     *      required=true,
     *          @OA\JsonContent(
     *              required={"name", "email", "password", "password_confirmation", "role"},
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password123"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *              @OA\Property(property="role", type="string", example="user | admin"),
     *              @OA\Property(property="country", type="string", nullable=true, example="Spain"),
     *              
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
    public function createUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::min(8)],
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 412);
        }

        $admin = Auth::user();
        if ($admin->group == 'admin') {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'job_role' => 'Happy Person',
    
            ]);       
            
            $user->syncRoles($request->role);
            $user->status = 'active';
            $user->group = $request->role;
            $user->email_verified_at = now();
            $user->referral_id = strtoupper(Str::random(15));
            $user->gpt_3_turbo_credits = config('settings.free_gpt_3_turbo_credits');
            $user->gpt_4_turbo_credits = config('settings.free_gpt_4_turbo_credits');
            $user->gpt_4_credits = config('settings.free_gpt_4_credits');
            $user->gpt_4o_credits = config('settings.free_gpt_4o_credits');
            $user->fine_tune_credits = config('settings.free_fine_tune_credits');
            $user->claude_3_opus_credits = config('settings.free_claude_3_opus_credits');
            $user->claude_3_sonnet_credits = config('settings.free_claude_3_sonnet_credits');
            $user->claude_3_haiku_credits = config('settings.free_claude_3_haiku_credits');
            $user->gemini_pro_credits = config('settings.free_gemini_pro_credits');
            $user->available_dalle_images = config('settings.free_tier_dalle_images');
            $user->available_sd_images = config('settings.free_tier_sd_images');
            $user->available_chars_prepaid = config('settings.voiceover_welcome_chars');
            $user->available_minutes_prepaid = config('settings.whisper_welcome_minutes');
            $user->default_voiceover_language = config('settings.voiceover_default_language');
            $user->default_voiceover_voice = config('settings.voiceover_default_voice');
            $user->save();        
    
            try {
                Mail::to($user)->send(new AddUser($request->email, $request->password));
            } catch (Exception $e) {
                \Log::info('SMTP settings are not setup to send payment notifications via email');
            }
    
            return response()->json('User successfully created', 201);
        } else {
            return response()->json(['error' => __('Unauthorized: No permission')], 403);
        }

        
    }



   /**
     * Delete user
     *
     * @OA\Delete(
     *      path="/api/v1/admin/user/delete",
     *      operationId="deleteUser",
     *      tags={"User Management"},
     *      summary="Delete user account",
     *      description="Deleted targeted user via his ID number, can be done only by admin group",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="user_id",
     *          in="path",
     *          description="ID of the user",
     *          required=true,
     *          @OA\Schema(type="integer", example="7"),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found",
     *      ),
     * )
    */
    public function deleteUser(Request $request)
    {
        if($request->user_id == null) return response()->json(['error' => __('User ID value missing.')], 412);

        $user = Auth::user();
        if ($user->group == 'admin') {
            $data = User::find(request('user_id'));
            if ($data) {
                $data->delete();
                return response()->json('User successfully deleted', 201);
            } else {
                return response()->json(['error' => __('User not found')], 404);
            }

        } else {
            return response()->json(['error' => __('Unauthorized: No permission')], 403);
        }
        
    }


    /**
     * Manually increase user balance
     *
     * @OA\Post(
     *      path="/api/v1/admin/user/increase-balance",
     *      operationId="increaseBalance",
     *      tags={"User Management"},
     *      summary="Increase user balance manually",
     *      description="Increase user balance manually by admin",
     *      security={{ "passport": {} }},
     *      @OA\RequestBody(
     *      required=true,
     *          @OA\JsonContent(
     *              required={"user_id"},
     *              @OA\Property(property="user_id", type="integer", example="7"),
     *              @OA\Property(property="gpt-3-turbo", type="integer", example="1000"),
     *              @OA\Property(property="gpt-4-turbo", type="integer", example="1000"),
     *              @OA\Property(property="gpt-4", type="integer", example="1000"),
     *              @OA\Property(property="gpt-4o", type="integer", example="1000"),
     *              @OA\Property(property="fine-tune", type="integer", example="1000"),
     *              @OA\Property(property="claude-3-opus", type="integer", example="1000"),
     *              @OA\Property(property="claude-3-sonnet", type="integer", example="1000"),
     *              @OA\Property(property="claude-3-haiku", type="integer", example="1000"),
     *              @OA\Property(property="gemini-pro", type="integer", example="1000"),
     *              @OA\Property(property="dalle-images", type="integer", example="1000"),
     *              @OA\Property(property="sd-images", type="integer", example="1000"),
     *              @OA\Property(property="chars", type="integer", example="1000"),
     *              @OA\Property(property="minutes", type="integer", example="1000"),
     *              @OA\Property(property="gpt-3-turbo-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="gpt-4-turbo-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="gpt-4-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="gpt-4o-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="fine-tune-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="claude-3-opus-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="claude-3-sonnet-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="claude-3-haiku-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="gemini-pro-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="dalle-images-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="sd-images-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="chars-prepaid", type="integer", example="1000"),
     *              @OA\Property(property="minutes-prepaid", type="integer", example="1000"),
     *              
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
    public function increaseBalance(Request $request)
    {
        if($request->user_id == null) return response()->json(['error' => __('User ID value missing.')], 412);

        $admin = Auth::user();
        if ($admin->group == 'admin') {
            $user = User::find(request('user_id'));
            if ($user) {
                
                $user->gpt_3_turbo_credits = request('gpt-3-turbo');
                $user->gpt_4_turbo_credits = request('gpt-4-turbo');
                $user->gpt_4_credits = request('gpt-4');
                $user->gpt_4o_credits = request('gpt-4o');
                $user->fine_tune_credits = request('fine-tune');
                $user->claude_3_opus_credits = request('claude-3-opus');
                $user->claude_3_sonnet_credits = request('claude-3-sonnet');
                $user->claude_3_haiku_credits = request('claude-3-haiku');
                $user->gemini_pro_credits = request('gemini-pro');
                $user->available_dalle_images =  request('dalle-images');
                $user->available_sd_images =  request('sd-images');
                $user->available_chars = request('chars');
                $user->available_minutes = request('minutes');
                $user->available_dalle_images_prepaid =  request('dalle-images-prepaid');
                $user->available_sd_images_prepaid =  request('sd-images-prepaid');
                $user->available_chars_prepaid = request('chars-prepaid');
                $user->available_minutes_prepaid = request('minutes-prepaid');
                $user->gpt_3_turbo_credits_prepaid = request('gpt-3-turbo-prepaid');
                $user->gpt_4_turbo_credits_prepaid = request('gpt-4-turbo-prepaid');
                $user->gpt_4_credits_prepaid = request('gpt-4-prepaid');
                $user->gpt_4o_credits_prepaid = request('gpt-4o-prepaid');
                $user->fine_tune_credits_prepaid = request('fine-tune-prepaid');
                $user->claude_3_opus_credits_prepaid = request('claude-3-opus-prepaid');
                $user->claude_3_sonnet_credits_prepaid = request('claude-3-sonnet-prepaid');
                $user->claude_3_haiku_credits_prepaid = request('claude-3-haiku-prepaid');
                $user->gemini_pro_credits_prepaid = request('gemini-pro-prepaid');
                $user->save();

                $words = 0;
                $dalle_images = request('dalle-images') + request('dalle_images_prepaid');
                $sd_images = request('sd-images') + request('sd_images_prepaid');
                $minutes = request('minutes') + request('minutes_prepaid');
                $chars = request('chars') + request('chars_prepaid');

                try {
                    Mail::to($user)->send(new AddCredits($words, $minutes, $chars, $dalle_images, $sd_images));
                } catch (Exception $e) {
                    \Log::info('SMTP settings are not setup to send payment notifications via email');
                }

                return response()->json('Credit were succesfully updated', 201);
            } else {
                return response()->json(['error' => __('User not found')], 404);
            }

        } else {
            return response()->json(['error' => __('Unauthorized: No permission')], 403);
        }
        
    }


    /**
     * Assign a plan to a user manually
     *
     * @OA\Post(
     *      path="/api/v1/admin/user/assign-subscription",
     *      operationId="assignSubscription",
     *      tags={"User Management"},
     *      summary="Increase user balance manually",
     *      description="Increase user balance manually by admin",
     *      security={{ "passport": {} }},
     *      @OA\RequestBody(
     *      required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="integer", example="7"),
     *              @OA\Property(property="plan_id", type="integer", example="1"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="User not found",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
    public function assignSubscription(Request $request)
    {
        if($request->user_id == null) return response()->json(['error' => __('User ID value missing.')], 412);
        if($request->plan_id == null) return response()->json(['error' => __('Plan ID value missing.')], 412);

        $admin = Auth::user();

        if ($admin->group == 'admin') {
            $user = User::find(request('user_id'));
            if ($user) {
                $plan = SubscriptionPlan::where('id', $request->plan_id)->first();
                if ($plan) {

                    if (!is_null($user->plan_id)) {
                        if ($user->plan_id == $request->plan) {
                            toastr()->warning(__('User has already this plan assigned, select a different plan'));
                            return redirect()->back();
                        } else {
                            $subscriber = Subscriber::where('status', 'Active')->where('user_id', $user->id)->first();
            
                            if ($subscriber) {
                                $this->stopSubscription($subscriber->id);
                            }
                        }
                    }
            
            
                    $subscription_id = strtoupper(Str::random(10));
            
                    switch ($plan->payment_frequency) {
                        case 'monthly':
                            $days = 30;
                            break;
                        case 'yearly':
                            $days = 365;
                            break;
                        case 'lifetime':
                            $days = 18250;
                            break;
                    }
            
                    Subscriber::create([
                        'user_id' => $user->id,
                        'plan_id' => $plan->id,
                        'status' => 'Active',
                        'created_at' => now(),
                        'gateway' => 'Manual',
                        'frequency' => $plan->payment_frequency,
                        'plan_name' => $plan->plan_name,
                        'gpt_3_turbo_credits' => $plan->gpt_3_turbo_credits,
                        'gpt_4_turbo_credits' => $plan->gpt_4_turbo_credits,
                        'gpt_4_credits' => $plan->gpt_4_credits,
                        'claude_3_opus_credits' => $plan->claude_3_opus_credits,
                        'claude_3_sonnet_credits' => $plan->claude_3_sonnet_credits,
                        'claude_3_haiku_credits' => $plan->claude_3_haiku_credits,
                        'gemini_pro_credits' => $plan->gemini_pro_credits,
                        'fine_tune_credits' => $plan->fine_tune_credits,
                        'dalle_images' => $plan->dalle_images,
                        'sd_images' => $plan->sd_images,
                        'characters' => $plan->characters,
                        'minutes' => $plan->minutes,
                        'subscription_id' => $subscription_id,
                        'active_until' => Carbon::now()->addDays($days),
                    ]);  
                    
            
                    $group = ($user->hasRole('admin')) ? 'admin' : 'subscriber';
            
                    $user->syncRoles($group);    
                    $user->group = $group;
                    $user->plan_id = $plan->id;
                    $user->gpt_3_turbo_credits = $plan->gpt_3_turbo_credits;
                    $user->gpt_4_turbo_credits = $plan->gpt_4_turbo_credits;
                    $user->gpt_4_credits = $plan->gpt_4_credits;
                    $user->gpt_4o_credits = $plan->gpt_4o_credits;
                    $user->fine_tune_credits = $plan->fine_tune_credits;
                    $user->claude_3_opus_credits = $plan->claude_3_opus_credits;
                    $user->claude_3_sonnet_credits = $plan->claude_3_sonnet_credits;
                    $user->claude_3_haiku_credits = $plan->claude_3_haiku_credits;
                    $user->gemini_pro_credits = $plan->gemini_pro_credits;
                    $user->available_dalle_images = $plan->dalle_images;
                    $user->available_sd_images = $plan->sd_images;
                    $user->available_chars = $plan->characters;
                    $user->available_minutes = $plan->minutes;
                    $user->member_limit = $plan->team_members;
                    $user->save(); 
            

                    return response()->json('Plan successfully assigned', 201);
                } else {
                    return response()->json(['error' => __('Plan not found')], 404);
                }
                
            } else {
                return response()->json(['error' => __('User not found')], 404);
            }

        } else {
            return response()->json(['error' => __('Unauthorized: No permission')], 403);
        }
    }


    /**
     * Cancel active subscription
     */
    public function stopSubscription($id)
    {   
            
        $id = Subscriber::where('id', $id)->first();

        if ($id->status == 'Cancelled') {
            $data['status'] = 200;
            $data['message'] = __('This subscription was already cancelled before');
            return $data;
        } elseif ($id->status == 'Suspended') {
            $data['status'] = 400;
            $data['message'] = __('Subscription has been suspended due to failed renewal payment');
            return $data;
        } elseif ($id->status == 'Expired') {
            $data['status'] = 400;
            $data['message'] = __('Subscription has been expired, please create a new one');
            return $data;
        }

        if ($id->frequency == 'lifetime') {
            $id->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())]);
            $user = User::where('id', $id->user_id)->firstOrFail();
            $user->plan_id = null;
            $user->group = 'user';
            $user->member_limit = null;
            $user->save();

            $data['status'] = 200;
            $data['message'] = __('Subscription has been successfully cancelled');
            return $data;

        } else {

            switch ($id->gateway) {
                case 'PayPal':
                    $platformID = 1;
                    break;
                case 'Stripe':
                    $platformID = 2;
                    break;
                case 'BankTransfer':
                    $platformID = 3;
                    break;
                case 'Paystack':
                    $platformID = 4;
                    break;
                case 'Razorpay':
                    $platformID = 5;
                    break;
                case 'Mollie':
                    $platformID = 7;
                    break;
                case 'Flutterwave':
                    $platformID = 10;
                    break;
                case 'Yookassa':
                    $platformID = 11;
                    break;
                case 'Paddle':
                    $platformID = 12;
                    break;
                case 'Manual':
                case 'FREE':
                    $platformID = 99;
                    break;
                default:
                    $platformID = 1;
                    break;
            }
            

            if ($id->gateway == 'PayPal' || $id->gateway == 'Stripe' || $id->gateway == 'Paystack' || $id->gateway == 'Razorpay' || $id->gateway == 'Mollie' || $id->gateway == 'Flutterwave' || $id->gateway == 'Yookassa' || $id->gateway == 'Paddle') {
                $paymentPlatform = $this->paymentPlatformResolver->resolveService($platformID);

                $status = $paymentPlatform->stopSubscription($id->subscription_id);

                if ($platformID == 2) {
                    if ($status) {
                        $id->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())]);
                        $user = User::where('id', $id->user_id)->firstOrFail();
                        $user->plan_id = null;
                        $user->group = 'user';
                        $user->member_limit = null;
                        $user->save();
                    }
                } elseif ($platformID == 4) {
                    if ($status->status) {
                        $id->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())]);
                        $user = User::where('id', $id->user_id)->firstOrFail();
                        $group = ($user->hasRole('admin'))? 'admin' : 'user';
                        $user->syncRoles($group); 
                        $user->plan_id = null;
                        $user->group = $group;
                        $user->member_limit = null;
                        $user->save();
                    }
                } elseif ($platformID == 5) {
                    if ($status->status == 'cancelled') {
                        $id->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())]);
                        $user = User::where('id', $id->user_id)->firstOrFail();
                        $group = ($user->hasRole('admin'))? 'admin' : 'user';
                        $user->syncRoles($group); 
                        $user->plan_id = null;
                        $user->group = $group;
                        $user->member_limit = null;
                        $user->save();
                    }
                } elseif ($platformID == 7) {
                    if ($status->status == 'Cancelled') {
                        $id->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())]);
                        $user = User::where('id', $id->user_id)->firstOrFail();
                        $group = ($user->hasRole('admin'))? 'admin' : 'user';
                        $user->syncRoles($group); 
                        $user->plan_id = null;
                        $user->group = $group;
                        $user->member_limit = null;
                        $user->save();
                    }
                } elseif ($platformID == 10) {
                    if ($status == 'cancelled') {
                        $id->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())]);
                        $user = User::where('id', $id->user_id)->firstOrFail();
                        $group = ($user->hasRole('admin'))? 'admin' : 'user';
                        $user->syncRoles($group); 
                        $user->plan_id = null;
                        $user->group = $group;
                        $user->member_limit = null;
                        $user->save();
                    }
                } elseif ($platformID == 11) {
                    if ($status == 'cancelled') {
                        $id->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())]);
                        $user = User::where('id', $id->user_id)->firstOrFail();
                        $group = ($user->hasRole('admin'))? 'admin' : 'user';
                        $user->syncRoles($group); 
                        $user->plan_id = null;
                        $user->group = $group;
                        $user->member_limit = null;
                        $user->save();
                    }
                } elseif ($platformID == 12) {
                    if ($status == 'cancelled') {
                        $id->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())]);
                        $user = User::where('id', $id->user_id)->firstOrFail();
                        $group = ($user->hasRole('admin'))? 'admin' : 'user';
                        $user->syncRoles($group); 
                        $user->plan_id = null;
                        $user->group = $group;
                        $user->member_limit = null;
                        $user->save();
                    }
                } elseif ($platformID == 99) { 
                    $id->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())]);
                    $user = User::where('id', $id->user_id)->firstOrFail();
                    $group = ($user->hasRole('admin'))? 'admin' : 'user';
                    $user->syncRoles($group); 
                    $user->plan_id = null;
                    $user->group = $group;
                    $user->member_limit = null;
                    $user->save();
                } else {
                    if (is_null($status)) {
                        $id->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())]);
                        $user = User::where('id', $id->user_id)->firstOrFail();
                        $group = ($user->hasRole('admin'))? 'admin' : 'user';
                        $user->syncRoles($group); 
                        $user->plan_id = null;
                        $user->group = $group;
                        $user->member_limit = null;
                        $user->save();
                    }
                }
            } else {
                $id->update(['status'=>'Cancelled', 'active_until' => Carbon::createFromFormat('Y-m-d H:i:s', now())]);
                $user = User::where('id', $id->user_id)->firstOrFail();
                $group = ($user->hasRole('admin'))? 'admin' : 'user';
                $user->syncRoles($group); 
                $user->plan_id = null;
                $user->group = $group;
                $user->member_limit = null;
                $user->save();
            }
            
            $data['status'] = 200;
            $data['message'] = __('Subscription has been successfully cancelled');
            return $data;
        }
    }

}   
