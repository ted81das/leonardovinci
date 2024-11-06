<?php

namespace App\Services;

use App\Http\Controllers\Admin\ExtensionController;
use Illuminate\Http\Request;
use Stripe\Stripe;

class StripeMarketplace 
{
    protected $baseURI;
    protected $sak;

    private $extensions;

    public function __construct()
    {
        $this->extensions = new ExtensionController();
        $this->sak = $this->extensions->sak();
    }


    /**
     * Process stripe payment
     *
     * @return \Illuminate\Http\Response
     */
    public function processStripe() 
    {
        if (session()->has('type')) {
            $slug = session()->get('name');
            $type = session()->get('type'); 
            $amount = session()->get('amount'); 
        }

        $name = "Payment for: " . ucfirst($slug) . ' ' . ucfirst($type);
        $total = $amount * 100;
        

        Stripe::setApiKey($this->sak);

       try {
            if ($type == 'theme') {
                $session = \Stripe\Checkout\Session::create([
                    'customer_email' => auth()->user()->email,
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' => 'USD',
                                'product_data' => [
                                    'name' => $name,
                                ],
                                'unit_amount' => $total,
                            ],
                            'quantity' => 1,
                        ]
                    ],
                    'mode' => 'payment',
                    'success_url' => route('admin.payments.theme.approved'),
                    'cancel_url' => route('admin.payments.stripe.theme.cancel'),
                ]);
            } else {
                $session = \Stripe\Checkout\Session::create([
                    'customer_email' => auth()->user()->email,
                    'line_items' => [
                        [
                            'price_data' => [
                                'currency' => 'USD',
                                'product_data' => [
                                    'name' => $name,
                                ],
                                'unit_amount' => $total,
                            ],
                            'quantity' => 1,
                        ]
                    ],
                    'mode' => 'payment',
                    'success_url' => route('admin.payments.market.approved'),
                    'cancel_url' => route('admin.payments.stripe.market.cancel'),
                ]);
            }

            if (!is_null($session->payment_intent)) {
                session()->put('paymentIntentID', $session->payment_intent);
            } else {
                session()->put('paymentIntentID', $session->id);
            }

        } catch (\Exception $e) {
            toastr()->error(__('Stripe authentication error, verify your stripe settings first ' . $e->getMessage()));
            return redirect()->back();
        } 

        return response()->json(['id' => $session->id, 'status' => 200]);
    }


    /**
     * Process stripe pament cancelation
     *
     * @return \Illuminate\Http\Response
    */
    public function processThemeCancel() 
    {
        toastr()->warning(__('Stripe payment has been cancelled'));
        return redirect()->route('admin.themes');
    }


    public function handleThemeApproval(Request $request)
    {
        $paymentIntentID = session()->get('paymentIntentID');
        $slug = session()->get('name');

        $theme = $this->extensions->verify($slug, $paymentIntentID);
        
        if ($theme) {
            session()->forget('paymentIntentID');
            session()->forget('name');
            toastr()->success(__('Payment Successfully Processed'));
            return view('admin.themes.success-theme', compact('theme'));
        } else {
            return redirect()->route('admin.themes');
        }
        
    }


    public function handleMarketApproval(Request $request)
    {
        $paymentIntentID = session()->get('paymentIntentID');
        $plan = session()->get('plan_id');
        $type = session()->get('type');
        $amount = session()->get('amount');     

        toastr()->success(__('Payment Successfully Processed'));
        return view('admin.themes.success-market');
    }


}