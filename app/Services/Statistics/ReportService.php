<?php

namespace App\Services\Statistics;
use App\Models\Content;
use App\Models\VendorPrice;
use App\Models\ChatHistory;
use App\Models\Image;
use App\Models\Payment;
use App\Models\Referral;
use App\Models\Payout;
use App\Models\User;
use DB;

class ReportService 
{
    private $year;
    private $month;
    private $cost;
    private $monthly_payment = 0;
    private $monthly_cost = 0;
    private $monthly_referrals = 0;
    private $monthly_payouts = 0;
    private $monthly_gpt_3t = 0;
    private $monthly_gpt_4t = 0;
    private $monthly_gpt_4 = 0;
    private $monthly_gpt_4o = 0;
    private $monthly_gpt_4o_mini = 0;
    private $monthly_opus = 0;
    private $monthly_sonnet = 0;
    private $monthly_haiku = 0;
    private $monthly_gemini = 0;
    private $monthly_users = 0;
    private $monthly_subscribers = 0;
    private $monthly_country = '';
    private $monthly_gateway = '';
    private $monthly_subscription_plan = '';
    
    private $yearly_payment = 0;
    private $yearly_cost = 0;
    private $yearly_referrals = 0;
    private $yearly_payouts = 0;
    private $yearly_gpt_3t = 0;
    private $yearly_gpt_4t = 0;
    private $yearly_gpt_4 = 0;
    private $yearly_gpt_4o = 0;
    private $yearly_gpt_4o_mini = 0;
    private $yearly_opus = 0;
    private $yearly_sonnet = 0;
    private $yearly_haiku = 0;
    private $yearly_gemini = 0;
    private $yearly_users = 0;
    private $yearly_subscribers = 0;
    private $yearly_country = '';
    private $yearly_gateway = '';
    private $yearly_subscription_plan = '';

    public function __construct(int $year = null, int $month = null) 
    {
        $this->year = $year;
        $this->month = $month;
        $this->cost = VendorPrice::first();
    }


    public function getTotalUsageCurrentMonth()
    {   
        $cost = Content::select(DB::raw("sum(tokens) as data, model"))->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->groupBy('model')->get();
        $gpt_3t = $cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first();
        $gpt_3t_cost = $gpt_3t/1000 * $this->cost->gpt_3t;
        $gpt_4t = $cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first();
        $gpt_4t_cost = $gpt_4t/1000 * $this->cost->gpt_4t;
        $gpt_4 = $cost->where('model', 'gpt-4')->pluck('data')->first();
        $gpt_4_cost = $gpt_4/1000 * $this->cost->gpt_4;
        $gpt_4o = $cost->where('model', 'gpt-4o')->pluck('data')->first();
        $gpt_4o_cost = $gpt_4o/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini = $cost->where('model', 'gpt-4o-mini')->pluck('data')->first();
        $gpt_4o_mini_cost = $gpt_4o_mini/1000 * $this->cost->gpt_4o_mini;
        $opus = $cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first();
        $opus_cost = $opus/1000 * $this->cost->claude_3_opus;
        $sonnet = $cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first();
        $sonnet_cost = $sonnet/1000 * $this->cost->claude_3_sonnet;
        $haiku = $cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first();
        $haiku_cost = $haiku/1000 * $this->cost->claude_3_haiku;
        $gemini = $cost->where('model', 'gemini_pro')->pluck('data')->first();           
        $gemini_cost = $gemini/1000 * $this->cost->gemini_pro;           

        $templates = $gpt_3t_cost + $gpt_4t_cost + $gpt_4_cost + $gpt_4o_cost + $gpt_4o_mini_cost + $opus_cost + $sonnet_cost + $haiku_cost + $gemini_cost;

        $cost = ChatHistory::select(DB::raw("sum(words) as data, model"))->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->groupBy('model')->get();           
        $gpt_3t_chat = $cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first();
        $gpt_3t_cost = $gpt_3t_chat/1000 * $this->cost->gpt_3t;
        $gpt_4t_chat = $cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first();
        $gpt_4t_cost = $gpt_4t_chat/1000 * $this->cost->gpt_4t;
        $gpt_4_chat = $cost->where('model', 'gpt-4')->pluck('data')->first();
        $gpt_4_cost = $gpt_4_chat/1000 * $this->cost->gpt_4;
        $gpt_4o_chat = $cost->where('model', 'gpt-4o')->pluck('data')->first();
        $gpt_4o_cost = $gpt_4o_chat/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini_chat = $cost->where('model', 'gpt-4o-mini')->pluck('data')->first();
        $gpt_4o_mini_cost = $gpt_4o_mini_chat/1000 * $this->cost->gpt_4o_mini;
        $opus_chat = $cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first();
        $opus_cost = $opus_chat/1000 * $this->cost->claude_3_opus;
        $sonnet_chat = $cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first();
        $sonnet_cost = $sonnet_chat/1000 * $this->cost->claude_3_sonnet;
        $haiku_chat = $cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first();
        $haiku_cost = $haiku_chat/1000 * $this->cost->claude_3_haiku;
        $gemini_chat = $cost->where('model', 'gemini_pro')->pluck('data')->first();           
        $gemini_cost = $gemini_chat/1000 * $this->cost->gemini_pro;           

        $chats = $gpt_3t_cost + $gpt_4t_cost + $gpt_4_cost + $gpt_4o_cost + $gpt_4o_mini_cost + $opus_cost + $sonnet_cost + $haiku_cost + $gemini_cost;

        $de_images = Image::select(DB::raw("count(id) as data, vendor_engine"))->where('vendor', 'dalle')->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->groupBy('vendor_engine')->get();
        $dalle_2 = ($de_images->where('vendor_engine', 'dall-e-2'))->pluck('data')->first() * $this->cost->dalle_2;
        $dalle_3 = ($de_images->where('vendor_engine', 'dall-e-3'))->pluck('data')->first() * $this->cost->dalle_3;
        $dalle_3_hd = ($de_images->where('vendor_engine', 'dall-e-3-hd'))->pluck('data')->first() * $this->cost->dalle_3_hd;
        $dalle = $dalle_2 + $dalle_3 + $dalle_3_hd;

        $sd_images = Image::select(DB::raw("sum(cost) as data"))->where('vendor', 'sd')->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->get();
        $sd = ($sd_images->pluck('data')->first()/1000) * $this->cost->sd;

        $this->monthly_cost = $chats + $templates + $dalle + $sd;

        $this->monthly_gpt_3t = $gpt_3t + $gpt_3t_chat;
        $this->monthly_gpt_4t = $gpt_4t + $gpt_4t_chat;
        $this->monthly_gpt_4 = $gpt_4 + $gpt_4_chat;
        $this->monthly_gpt_4o = $gpt_4o + $gpt_4o_chat;
        $this->monthly_gpt_4o_mini = $gpt_4o_mini + $gpt_4o_mini_chat;
        $this->monthly_opus = $opus + $opus_chat;
        $this->monthly_sonnet = $sonnet + $sonnet_chat;
        $this->monthly_haiku = $haiku + $haiku_chat;
        $this->monthly_gemini = $gemini + $gemini_chat;
    }


    public function getTotalUsageCurrentYear()
    {   
        $cost = Content::select(DB::raw("sum(tokens) as data, model"))->whereYear('created_at', $this->year)->groupBy('model')->get();
        $gpt_3t = $cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first();
        $gpt_3t_cost = $gpt_3t/1000 * $this->cost->gpt_3t;
        $gpt_4t = $cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first();
        $gpt_4t_cost = $gpt_4t/1000 * $this->cost->gpt_4t;
        $gpt_4 = $cost->where('model', 'gpt-4')->pluck('data')->first();
        $gpt_4_cost = $gpt_4/1000 * $this->cost->gpt_4;
        $gpt_4o = $cost->where('model', 'gpt-4o')->pluck('data')->first();
        $gpt_4o_cost = $gpt_4o/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini = $cost->where('model', 'gpt-4o-mini')->pluck('data')->first();
        $gpt_4o_mini_cost = $gpt_4o_mini/1000 * $this->cost->gpt_4o_mini;
        $opus = $cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first();
        $opus_cost = $opus/1000 * $this->cost->claude_3_opus;
        $sonnet = $cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first();
        $sonnet_cost = $sonnet/1000 * $this->cost->claude_3_sonnet;
        $haiku = $cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first();
        $haiku_cost = $haiku/1000 * $this->cost->claude_3_haiku;
        $gemini = $cost->where('model', 'gemini_pro')->pluck('data')->first();           
        $gemini_cost = $gemini/1000 * $this->cost->gemini_pro;           

        $templates = $gpt_3t_cost + $gpt_4t_cost + $gpt_4_cost + $gpt_4o_cost + $gpt_4o_mini_cost + $opus_cost + $sonnet_cost + $haiku_cost + $gemini_cost;

        $cost = ChatHistory::select(DB::raw("sum(words) as data, model"))->whereYear('created_at', $this->year)->groupBy('model')->get();           
        $gpt_3t_chat = $cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first();
        $gpt_3t_cost = $gpt_3t_chat/1000 * $this->cost->gpt_3t;
        $gpt_4t_chat = $cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first();
        $gpt_4t_cost = $gpt_4t_chat/1000 * $this->cost->gpt_4t;
        $gpt_4_chat = $cost->where('model', 'gpt-4')->pluck('data')->first();
        $gpt_4_cost = $gpt_4_chat/1000 * $this->cost->gpt_4;
        $gpt_4o_chat = $cost->where('model', 'gpt-4o')->pluck('data')->first();
        $gpt_4o_cost = $gpt_4o_chat/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini_chat = $cost->where('model', 'gpt-4o-mini')->pluck('data')->first();
        $gpt_4o_mini_cost = $gpt_4o_mini_chat/1000 * $this->cost->gpt_4o_mini;
        $opus_chat = $cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first();
        $opus_cost = $opus_chat/1000 * $this->cost->claude_3_opus;
        $sonnet_chat = $cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first();
        $sonnet_cost = $sonnet_chat/1000 * $this->cost->claude_3_sonnet;
        $haiku_chat = $cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first();
        $haiku_cost = $haiku_chat/1000 * $this->cost->claude_3_haiku;
        $gemini_chat = $cost->where('model', 'gemini_pro')->pluck('data')->first();           
        $gemini_cost = $gemini_chat/1000 * $this->cost->gemini_pro;           

        $chats = $gpt_3t_cost + $gpt_4t_cost + $gpt_4_cost + $gpt_4o_cost + $gpt_4o_mini_cost + $opus_cost + $sonnet_cost + $haiku_cost + $gemini_cost;

        $de_images = Image::select(DB::raw("count(id) as data, vendor_engine"))->where('vendor', 'dalle')->whereYear('created_at', $this->year)->groupBy('vendor_engine')->get();
        $dalle_2 = ($de_images->where('vendor_engine', 'dall-e-2'))->pluck('data')->first() * $this->cost->dalle_2;
        $dalle_3 = ($de_images->where('vendor_engine', 'dall-e-3'))->pluck('data')->first() * $this->cost->dalle_3;
        $dalle_3_hd = ($de_images->where('vendor_engine', 'dall-e-3-hd'))->pluck('data')->first() * $this->cost->dalle_3_hd;
        $dalle = $dalle_2 + $dalle_3 + $dalle_3_hd;

        $sd_images = Image::select(DB::raw("sum(cost) as data"))->where('vendor', 'sd')->whereYear('created_at', $this->year)->get();
        $sd = ($sd_images->pluck('data')->first()/1000) * $this->cost->sd;

        $this->yearly_cost = $chats + $templates + $dalle + $sd;

        $this->yearly_gpt_3t = $gpt_3t + $gpt_3t_chat;
        $this->yearly_gpt_4t = $gpt_4t + $gpt_4t_chat;
        $this->yearly_gpt_4 = $gpt_4 + $gpt_4_chat;
        $this->yearly_gpt_4o = $gpt_4o + $gpt_4o_chat;
        $this->yearly_gpt_4o_mini = $gpt_4o_mini + $gpt_4o_mini_chat;
        $this->yearly_opus = $opus + $opus_chat;
        $this->yearly_sonnet = $sonnet + $sonnet_chat;
        $this->yearly_haiku = $haiku + $haiku_chat;
        $this->yearly_gemini = $gemini + $gemini_chat;
    }


    public function getTotalPaymentsCurrentMonth()
    {   
        $payments = Payment::select(DB::raw("sum(price) as data"))->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->where('status', 'completed')->get();          
        $this->monthly_payment = $payments[0]['data'];

        $payments = Payment::select(DB::raw("count(id) as data, gateway"))->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->where('status', 'completed')->groupBy('gateway')->orderBy('data', 'desc')->get()->toArray();          
        $this->monthly_gateway = ($payments) ? $payments[0]['gateway'] : 0;

        $payments = Payment::select(DB::raw("count(id) as data, plan_name"))->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->where('status', 'completed')->groupBy('plan_name')->orderBy('data', 'desc')->get()->toArray();          
        $this->monthly_subscription_plan = ($payments)? $payments[0]['plan_name'] : "";

        $payments = Referral::select(DB::raw("sum(payment) as data"))->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->get();          
        $this->monthly_referrals = $payments[0]['data'];

        $payments = Payout::select(DB::raw("sum(total) as data"))->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->where('status', 'completed')->get();  
        $this->monthly_payouts = $payments[0]['data'];
        
    }


    public function getTotalPaymentsCurrentYear()
    {   
        $payments = Payment::select(DB::raw("sum(price) as data"))->whereYear('created_at', $this->year)->where('status', 'completed')->get();          
        $this->yearly_payment = $payments[0]['data'];

        $payments = Payment::select(DB::raw("count(id) as data, gateway"))->whereYear('created_at', $this->year)->where('status', 'completed')->groupBy('gateway')->orderBy('data', 'desc')->get()->toArray();          
        $this->yearly_gateway = ($payments) ? $payments[0]['gateway'] : 0;

        $payments = Payment::select(DB::raw("count(id) as data, plan_name"))->whereYear('created_at', $this->year)->where('status', 'completed')->groupBy('plan_name')->orderBy('data', 'desc')->get()->toArray();          
        $this->yearly_subscription_plan = ($payments)? $payments[0]['plan_name'] : "";

        $payments = Referral::select(DB::raw("sum(payment) as data"))->whereYear('created_at', $this->year)->get();          
        $this->yearly_referrals = $payments[0]['data'];

        $payments = Payout::select(DB::raw("sum(total) as data"))->whereYear('created_at', $this->year)->where('status', 'completed')->get();  
        $this->yearly_payouts = $payments[0]['data'];
        
    }


    public function getTotalUsersCurrentMonth()
    {   
        $users = User::select(DB::raw("count(id) as data"))->whereYear('created_at', $this->year)->whereMonth('created_at', $this->month)->where('group', 'subscriber')->get();  
        $this->monthly_subscribers = $users[0]['data'];

        $users = User::select(DB::raw("count(id) as data"))->whereYear('created_at', $this->year)->whereMonth('created_at', $this->month)->where('group', 'user')->get();  
        $this->monthly_users = $users[0]['data'];

        $users = User::select(DB::raw("count(id) as data, country"))->whereYear('created_at', $this->year)->whereMonth('created_at', $this->month)->groupBy('country')->orderBy('data', 'desc')->get()->toArray();  
        $this->monthly_country = ($users) ? $users[0]['country']: "";
    }


    public function getTotalUsersCurrentYear()
    {   
        $users = User::select(DB::raw("count(id) as data"))->whereYear('created_at', $this->year)->where('group', 'subscriber')->get();  
        $this->yearly_subscribers = $users[0]['data'];

        $users = User::select(DB::raw("count(id) as data"))->whereYear('created_at', $this->year)->where('group', 'user')->get();  
        $this->yearly_users = $users[0]['data'];

        $users = User::select(DB::raw("count(id) as data, country"))->whereYear('created_at', $this->year)->groupBy('country')->orderBy('data', 'desc')->get()->toArray();  
        $this->yearly_country = ($users) ? $users[0]['country']: "";
    }





    public function monthlyReport()
    {
        $this->getTotalUsageCurrentMonth();
        $this->getTotalPaymentsCurrentMonth();
        $this->getTotalUsersCurrentMonth();

        $data = [];
        $data['revenue'] = $this->monthly_payment;
        $data['cost'] = $this->monthly_cost;
        $data['referrals'] = $this->monthly_referrals;
        $data['payouts'] = $this->monthly_payouts;
        $data['gateway'] = $this->monthly_gateway;
        $data['plan'] = $this->monthly_subscription_plan;

        $data['users'] = $this->monthly_users;
        $data['subscribers'] = $this->monthly_subscribers;
        $data['country'] = $this->monthly_country;

        $data['gpt_3t'] = $this->monthly_gpt_3t;
        $data['gpt_4t'] = $this->monthly_gpt_4t;
        $data['gpt_4'] = $this->monthly_gpt_4;
        $data['gpt_4o'] = $this->monthly_gpt_4o;
        $data['gpt_4o_mini'] = $this->monthly_gpt_4o_mini;
        $data['opus'] = $this->monthly_opus;
        $data['sonnet'] = $this->monthly_sonnet;
        $data['haiku'] = $this->monthly_haiku;
        $data['gemini'] = $this->monthly_gemini;
        
        return $data;
    }


    public function yearlyReport()
    {
        $this->getTotalUsageCurrentYear();
        $this->getTotalPaymentsCurrentYear();
        $this->getTotalUsersCurrentYear();

        $data = [];
        $data['revenue'] = $this->yearly_payment;
        $data['cost'] = $this->yearly_cost;
        $data['referrals'] = $this->yearly_referrals;
        $data['payouts'] = $this->yearly_payouts;
        $data['gateway'] = $this->yearly_gateway;
        $data['plan'] = $this->yearly_subscription_plan;

        $data['users'] = $this->yearly_users;
        $data['subscribers'] = $this->yearly_subscribers;
        $data['country'] = $this->yearly_country;

        $data['gpt_3t'] = $this->yearly_gpt_3t;
        $data['gpt_4t'] = $this->yearly_gpt_4t;
        $data['gpt_4'] = $this->yearly_gpt_4;
        $data['gpt_4o'] = $this->yearly_gpt_4o;
        $data['gpt_4o_mini'] = $this->yearly_gpt_4o_mini;
        $data['opus'] = $this->yearly_opus;
        $data['sonnet'] = $this->yearly_sonnet;
        $data['haiku'] = $this->yearly_haiku;
        $data['gemini'] = $this->yearly_gemini;
        
        return $data;
    }


}