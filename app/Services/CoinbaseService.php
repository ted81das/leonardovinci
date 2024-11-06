<?php

namespace App\Services;

use Illuminate\Http\Request;
use Spatie\Backup\Listeners\Listener;
use App\Services\Statistics\UserService;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\BadResponseException;
use App\Models\PrepaidPlan;
use App\Models\SubscriptionPlan;
use App\Services\HelperService;


class CoinbaseService 
{

    protected $client;
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

        $this->client = new HttpClient();     
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

        $listener = new Listener();
        $process = $listener->download();
        if (!$process['status']) return false;
        
        try {
            $coinbase_request = $this->client->request('POST', 'https://api.commerce.coinbase.com/charges', [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'X-CC-Api-Key' => config('services.coinbase.api_key'),
                        'X-CC-Version' => '2018-03-22',
                    ],
                    'body' => json_encode(array_merge_recursive([
                        'name' => 'Plan Name: '. $id->plan_name,
                        'description' => 'Included Characters: '. number_format($id->characters) . '; Included Minutes: ' . number_format($id->minutes),
                        'local_price' => [
                            'amount' => $total_value,
                            'currency' => $request->currency,
                        ],
                        'pricing_type' => 'fixed_price',
                        'metadata' => [
                            'user' => $request->user()->id,
                            'plan_id' => $id->id,
                            'type' => $type,
                            'amount' => $total_value,
                            'currency' => $request->currency,
                        ],
                        'redirect_url' => route('user.payments.approved'),
                        'cancel_url' => route('user.payments.cancelled'),
                    ]))
                ]
            );


            $coinbase = json_decode($coinbase_request->getBody()->getContents());

            $this->recordPayment($coinbase->data->code, $id, $total_value, $request->currency, $type);

            session()->put('order_coinbase', $coinbase->data->code);
            session()->put('plan_coinbase', $id);
            session()->put('type', $type);
          
        } catch (BadResponseException $e) {
            return back()->with('error', 'Coinbase authentication error.' . $e->getResponse()->getBody()->getContents());
        }

        return redirect($coinbase->data->hosted_url);
    }


    public function recordPayment($payment_id, $plan, $amount, $currency, $type)
    {        
        if ($type == 'lifetime') {

            $days = 18250;

            HelperService::registerSubscriber($plan, 'Coinbase', 'Pending', $payment_id, $days);
 
        }

        $payment = HelperService::registerPayment($type, $plan->id, $payment_id, $amount, 'Coinbase', 'pending');
    }


    public function handleApproval()
    {
        $order_id = session()->get('order_coinbase');
        $plan = session()->get('plan_coinbase');

        return view('user.plans.success', compact('plan', 'order_id'));
    }

}