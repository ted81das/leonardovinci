<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerification;
use Illuminate\Auth\Events\Verified;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Mail\WelcomeMessage;
use Carbon\Carbon;
use Exception;

use Spatie\Permission\Traits\HasRoles;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/auth/login",
     *      operationId="login",
     *      tags={"Authentication"},
     *      summary="Login",
     *      description="Login with the provided data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email", "password"},
     *              @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password123"),
     *              @OA\Property(property="remember_me", type="boolean", nullable=true),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *      ),
     * )
    */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:255'],
            'remember_me' => 'boolean'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $credentials = request(['email', 'password']);

        if (!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);

        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');

        $token = $tokenResult->token;
        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse($token->expires_at)->toDateTimeString()
        ]);
    }

     /**
     * @OA\Post(
     *      path="/api/auth/register",
     *      operationId="register",
     *      tags={"Authentication"},
     *      summary="Register a new user",
     *      description="Register a new user with the provided data",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name", "email", "password", "password_confirmation", "agreement", "country"},
     *              @OA\Property(property="name", type="string", example="John Doe"),
     *              @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *              @OA\Property(property="password", type="string", format="password", example="password123"),
     *              @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *              @OA\Property(property="country", type="string", nullable=true, example="Spain"),
     *              @OA\Property(property="agreement", type="boolean"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *      ),
     * )
    */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::min(8)],
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'country' => $request->country
        ]);
        
        event(new Registered($user));

        $referral_code = ($request->hasCookie('referral')) ? $request->cookie('referral') : ''; 
        $referrer = ($referral_code != '') ? User::where('referral_id', $referral_code)->firstOrFail() : '';
        $referrer_id = ($referrer != '') ? $referrer->id : '';

        $status = (config('settings.email_verification') == 'disabled') ? 'active' : 'pending';
        
        $user->assignRole(config('settings.default_user'));
        $user->status = $status;
        $user->group = config('settings.default_user');
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
        $user->available_chars = config('settings.voiceover_welcome_chars');
        $user->available_minutes = config('settings.whisper_welcome_minutes');
        $user->default_voiceover_language = config('settings.voiceover_default_language');
        $user->default_voiceover_voice = config('settings.voiceover_default_voice');
        $user->default_template_language = config('settings.default_language');
        $user->default_model_template = config('settings.default_model_user_template');
        $user->default_model_chat = config('settings.default_model_user_bot');
        $user->job_role = 'Happy Person';
        $user->referral_id = strtoupper(Str::random(15));
        $user->referred_by = $referrer_id;
        $user->email_opt_in = false;
        $user->save();     
        
        if (config('settings.email_verification') == 'enabled') {

            $digits = '0123456789';
            $digitsLength = strlen($digits);
            $code = '';
            for ($i = 0; $i < 6; $i++) {
                $code .= $digits[rand(0, $digitsLength - 1)];
            }
            $user->verification_code = $code;
            $user->save();

            try {
                Mail::to($user->email)->send(new EmailVerification($code));
            } catch (Exception $e) {
            }

        } else {
                
            event(new Verified($request->user()));

            $user->email_verified_at = now();
            $user->status = 'active';
            $user->save();

            try {
                Mail::to($request->user())->send(new WelcomeMessage());
            } catch (Exception $e) {
                \Log::info('SMTP settings are not configured yet');
            }
            
        }

        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
                      
    }


    /**
     * @OA\Post(
     *      path="/api/auth/logout",
     *      operationId="logout",
     *      tags={"Authentication"},
     *      summary="Logout the authenticated user",
     *      description="Logs out the authenticated user and revokes the access token",
     *      security={{ "passport": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successfully logged out",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     * )
    */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }


    /**
     * @OA\Post(
     *      path="/api/auth/email/verify/resend",
     *      operationId="emailVerificationCode",
     *      tags={"Authentication"},
     *      summary="Request email verification code",
     *      description="Register email verification code by user email address",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"email"},
     *              @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successfully sent",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *      ),
     * )
    */
    public function requestEmailVerificationCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $digits = '0123456789';
        $digitsLength = strlen($digits);
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $digits[rand(0, $digitsLength - 1)];
        }

        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->verification_code = $code;
            $user->save();
            try {
                Mail::to($request->user())->send(new EmailVerification($code));
                return response()->json(['message' => __("Email verification code has been sent successfully")], 200);
            } catch (Exception $e) {
                return response()->json(['error' => __("SMTP issue, contact support team")], 405);
            }
        } else {
            return response()->json(['error' => __("User not found")], 404);
        }
                      
    }


    /**
     * @OA\Post(
     *      path="/api/auth/email/verify",
     *      operationId="emailVerify",
     *      tags={"Authentication"},
     *      summary="Verify user's email address",
     *      description="Verify user's email address with his verification code",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"verification_code"},
     *              @OA\Property(property="verification_code", type="string"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *      ),
     * )
    */
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'verification_code' => 'required|string',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $user = User::where('verification_code', $request->verification_code)->first();

        if ($user) {
            $user->markEmailAsVerified();
                
            event(new Verified($user));

            $user->status = 'active';
            $user->save();
            return response()->json(['message' => 'Email verified successfully'], 201);
        } else {
            return response()->json(['error' => __("User not found")], 404);
        }
                      
    }


    

}
