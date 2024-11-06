<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Events\PayoutRequested;
use App\Models\Setting;
use App\Models\Referral;
use App\Models\Payout;
use App\Models\Payment;
use App\Models\User;
use DataTables;
use DB;

class ReferralController extends Controller
{
    /**
     * Get referral information
     *
     * @OA\Get(
     *      path="/api/v1/referrals/",
     *      operationId="referrals",
     *      tags={"Affiliate Program"},
     *      summary="Get referral information of current user",
     *      description="Get detailed referral information of the current user such as: number of referred users, total earnings and withdrawals",
     *      security={{ "passport": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="total_commission", type="integer", description="Total commission that the user earned"),
     *              @OA\Property(property="total_referred", type="integer", description="Total number of people that the user referred"),
     *              @OA\Property(property="total_withdrawal", type="integer", description="Total amount that the user requested to withdraw so far"),
     *              @OA\Property(property="referral_code", type="string", description="user referral code"),
     *              @OA\Property(property="currency", type="string", description="Currency symbol in ascii code"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     * )
    */
    public function referrals(Request $request)
    {
        $user = Auth::user();

        $total_commission = Referral::select(DB::raw("sum(commission) as data"))->where('referrer_id', $user->id)->get();
        $total_referred = User::select(DB::raw("count(referred_by) as data"))->where('referred_by', $user->id)->get();
        $total_withdrawal = Payout::select(DB::raw("sum(total) as data"))->where('user_id', $user->id)->get();


        return response()->json([
            "total_commission" => $total_commission,
            "total_referred" => $total_referred,
            "total_withdrawal" => $total_withdrawal,
            "referral_code" => $user->referral_id,
            "currency" => config('payment.default_system_currency_symbol'),
        ], 200);

    }


    /**
     * Requesting payout amount by the user
     *
     * @OA\Post(
     *      path="/api/v1/referrals/payouts/request",
     *      operationId="requestPayout",
     *      tags={"Affiliate Program"},
     *      summary="Set referral settings",
     *      description="Payout request by the user with their preferred payout method",
     *      security={{ "passport": {} }},
     *      @OA\RequestBody(
    *         required=true,
    *         description="Affiliate settings information",
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 type="object",
    *                 @OA\Property(
    *                     property="payout",
    *                     description="Payout amount",
    *                     type="float"
    *                 ),
    *                 @OA\Property(
    *                     property="bank_requisites",
    *                     description="Bank Requisites of the user",
    *                     type="string"
    *                 ),
    *                 
    *             ),
    *         ),
    *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Settings saved",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *       ),
     * )
    */
    public function payoutRequest(Request $request)
    {        
        if($request->payout == null) return response()->json(['error' => __('Payout value is missing.')], 412);
        if($request->bank_requisites == null) return response()->json(['error' => __('Bank requisites are missing.')], 412);

        $user = Auth::user();

        if ($request->payout > $user->balance) {
            return response()->json(['error' => __('Requested payout amount is more than your current balance')], 412);
        }

        if ($request->payout < config('payment.referral.payment.threshold')) {
            return response()->json(['error' => __('Requested payout amount is less than the current threshold')], 412);
        }        

        $user = User::where('id', $user->id)->first();   
        $user->balance = ($user->balance - $request->payout);
        $user->save();

        Payout::create([
            'request_id' => strtoupper(Str::random(15)),
            'user_id' => $user->id,
            'total' => $request->payout,
            'gateway' => request('payment_method'),
            'status' => 'processing',
        ]);       

        event(new PayoutRequested($user));
     
        return response()->json(['message' => 'Affiliate Payout Requested'], 201);
    }


    /**
     * Set referral settings - admin
     *
     * @OA\Post(
     *      path="/api/v1/referrals/settings",
     *      operationId="referralSettings",
     *      tags={"Affiliate Program"},
     *      summary="Set referral settings",
     *      description="Setup referral settings for your users, available only for admin group",
     *      security={{ "passport": {} }},
     *      @OA\RequestBody(
    *         required=true,
    *         description="Affiliate settings information",
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 type="object",
    *                 @OA\Property(
    *                     property="status",
    *                     description="Referral System Status: true - Enabled, false - Disabled",
    *                     type="boolean",
    *                     example="true"
    *                 ),
    *                 @OA\Property(
    *                     property="policy",
    *                     description="Payout policy for referral subscriptions. Allowed values: all | first",
    *                     type="string"
    *                 ),
    *                 @OA\Property(
    *                     property="commission",
    *                     description="Value of the percentage you want to give to your users for each referral subscription",
    *                     type="integer"
    *                 ),
    *                 @OA\Property(
    *                     property="threshold",
    *                     description="Minium value you want to set for withdrawal thresholds",
    *                     type="integer"
    *                 ),
    *             ),
    *         ),
    *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Settings saved",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *       ),
     * )
    */
    public function settings(Request $request)
    {
        if($request->status == null) return response()->json(['error' => __('Status is missing.')], 412);
        if($request->policy == null) return response()->json(['error' => __('Policy is missing.')], 412);
        if($request->commission == null) return response()->json(['error' => __('Comission is missing.')], 412);
        if($request->threshold == null) return response()->json(['error' => __('Threshold is missing.')], 412);

        $user = Auth::user();
        if ($user->group == 'admin') {
            $status = ($request->status == true) ? 'on' : '';
            $this->storeConfiguration('REFERRAL_SYSTEM_ENABLE', $status);
            $this->storeConfiguration('REFERRAL_USER_PAYMENT_POLICY', request('policy'));
            $this->storeConfiguration('REFERRAL_USER_PAYMENT_COMMISSION', request('commission'));
            $this->storeConfiguration('REFERRAL_USER_PAYMENT_THRESHOLD', request('threshold'));
            return response()->json(['message' => 'Settings saved'], 201);
        } else {
            return response()->json(['error' => __('Unauthorized request.')], 403);
        }

    }


    /**
     * Record in .env file
     */
    private function storeConfiguration($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {

            file_put_contents($path, str_replace(
                $key . '=' . env($key), $key . '=' . $value, file_get_contents($path)
            ));

        }
    }


}
