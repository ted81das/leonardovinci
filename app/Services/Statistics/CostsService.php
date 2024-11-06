<?php

namespace App\Services\Statistics;
use App\Models\Content;
use App\Models\VendorPrice;
use App\Models\ChatHistory;
use App\Models\Image;
use DB;

class CostsService 
{
    private $year;
    private $month;
    private $cost;

    public function __construct(int $year = null, int $month = null) 
    {
        $this->year = $year;
        $this->month = $month;
        $this->cost = VendorPrice::first();
    }


    public function getTotalSpending()
    {   
        $cost = Content::select(DB::raw("sum(tokens) as data, model"))->groupBy('model')->get();
        $gpt_3t = ($cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first())/1000 * $this->cost->gpt_3t;
        $gpt_4t = ($cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first())/1000 * $this->cost->gpt_4t;
        $gpt_4 = ($cost->where('model', 'gpt-4')->pluck('data')->first())/1000 * $this->cost->gpt_4;
        $gpt_4o = ($cost->where('model', 'gpt-4o')->pluck('data')->first())/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini = ($cost->where('model', 'gpt-4o-mini')->pluck('data')->first())/1000 * $this->cost->gpt_4o_mini;
        $opus = ($cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first())/1000 * $this->cost->claude_3_opus;
        $sonnet = ($cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first())/1000 * $this->cost->claude_3_sonnet;
        $haiku = ($cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first())/1000 * $this->cost->claude_3_haiku;
        $gemini = ($cost->where('model', 'gemini_pro')->pluck('data')->first())/1000 * $this->cost->gemini_pro;           

        $templates = $gpt_3t + $gpt_4t + $gpt_4 + $gpt_4o + $gpt_4o_mini + $opus + $sonnet + $haiku + $gemini;


        $cost = ChatHistory::select(DB::raw("sum(words) as data, model"))->groupBy('model')->get();           
        $gpt_3t = ($cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first())/1000 * $this->cost->gpt_3t;
        $gpt_4t = ($cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first())/1000 * $this->cost->gpt_4t;
        $gpt_4 = ($cost->where('model', 'gpt-4')->pluck('data')->first())/1000 * $this->cost->gpt_4;
        $gpt_4o = ($cost->where('model', 'gpt-4o')->pluck('data')->first())/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini = ($cost->where('model', 'gpt-4o-mini')->pluck('data')->first())/1000 * $this->cost->gpt_4o_mini;
        $opus = ($cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first())/1000 * $this->cost->claude_3_opus;
        $sonnet = ($cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first())/1000 * $this->cost->claude_3_sonnet;
        $haiku = ($cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first())/1000 * $this->cost->claude_3_haiku;
        $gemini = ($cost->where('model', 'gemini_pro')->pluck('data')->first())/1000 * $this->cost->gemini_pro;           

        $chats = $gpt_3t + $gpt_4t + $gpt_4 + $gpt_4o + $gpt_4o_mini + $opus + $sonnet + $haiku + $gemini;

        $de_images = Image::select(DB::raw("count(id) as data, vendor_engine"))->where('vendor', 'dalle')->groupBy('vendor_engine')->get();
        $dalle_2 = ($de_images->where('vendor_engine', 'dall-e-2'))->pluck('data')->first() * $this->cost->dalle_2;
        $dalle_3 = ($de_images->where('vendor_engine', 'dall-e-3'))->pluck('data')->first() * $this->cost->dalle_3;
        $dalle_3_hd = ($de_images->where('vendor_engine', 'dall-e-3-hd'))->pluck('data')->first() * $this->cost->dalle_3_hd;
        $dalle = $dalle_2 + $dalle_3 + $dalle_3_hd;

        $sd_images = Image::select(DB::raw("sum(cost) as data"))->where('vendor', 'sd')->get();
        $sd = ($sd_images->pluck('data')->first()/1000) * $this->cost->sd;

        return $chats + $templates + $dalle + $sd;
    }


    public function getTotalSpendingCurrentMonth()
    {   
        $cost = Content::select(DB::raw("sum(tokens) as data, model"))->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->groupBy('model')->get();
        $gpt_3t = ($cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first())/1000 * $this->cost->gpt_3t;
        $gpt_4t = ($cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first())/1000 * $this->cost->gpt_4t;
        $gpt_4 = ($cost->where('model', 'gpt-4')->pluck('data')->first())/1000 * $this->cost->gpt_4;
        $gpt_4o = ($cost->where('model', 'gpt-4o')->pluck('data')->first())/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini = ($cost->where('model', 'gpt-4o-mini')->pluck('data')->first())/1000 * $this->cost->gpt_4o_mini;
        $opus = ($cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first())/1000 * $this->cost->claude_3_opus;
        $sonnet = ($cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first())/1000 * $this->cost->claude_3_sonnet;
        $haiku = ($cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first())/1000 * $this->cost->claude_3_haiku;
        $gemini = ($cost->where('model', 'gemini_pro')->pluck('data')->first())/1000 * $this->cost->gemini_pro;           

        $templates = $gpt_3t + $gpt_4t + $gpt_4 + $gpt_4o + $gpt_4o_mini + $opus + $sonnet + $haiku + $gemini;

        $cost = ChatHistory::select(DB::raw("sum(words) as data, model"))->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->groupBy('model')->get();           
        $gpt_3t = ($cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first())/1000 * $this->cost->gpt_3t;
        $gpt_4t = ($cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first())/1000 * $this->cost->gpt_4t;
        $gpt_4 = ($cost->where('model', 'gpt-4')->pluck('data')->first())/1000 * $this->cost->gpt_4;
        $gpt_4o = ($cost->where('model', 'gpt-4o')->pluck('data')->first())/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini = ($cost->where('model', 'gpt-4o-mini')->pluck('data')->first())/1000 * $this->cost->gpt_4o_mini;
        $opus = ($cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first())/1000 * $this->cost->claude_3_opus;
        $sonnet = ($cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first())/1000 * $this->cost->claude_3_sonnet;
        $haiku = ($cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first())/1000 * $this->cost->claude_3_haiku;
        $gemini = ($cost->where('model', 'gemini_pro')->pluck('data')->first())/1000 * $this->cost->gemini_pro;           

        $chats = $gpt_3t + $gpt_4t + $gpt_4 + $gpt_4o + $gpt_4o_mini + $opus + $sonnet + $haiku + $gemini;

        $de_images = Image::select(DB::raw("count(id) as data, vendor_engine"))->where('vendor', 'dalle')->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->groupBy('vendor_engine')->get();
        $dalle_2 = ($de_images->where('vendor_engine', 'dall-e-2'))->pluck('data')->first() * $this->cost->dalle_2;
        $dalle_3 = ($de_images->where('vendor_engine', 'dall-e-3'))->pluck('data')->first() * $this->cost->dalle_3;
        $dalle_3_hd = ($de_images->where('vendor_engine', 'dall-e-3-hd'))->pluck('data')->first() * $this->cost->dalle_3_hd;
        $dalle = $dalle_2 + $dalle_3 + $dalle_3_hd;

        $sd_images = Image::select(DB::raw("sum(cost) as data"))->where('vendor', 'sd')->whereMonth('created_at', $this->month)->whereYear('created_at', $this->year)->get();
        $sd = ($sd_images->pluck('data')->first()/1000) * $this->cost->sd;

        return $chats + $templates + $dalle + $sd;
    }


    public function getTotalSpendingPastMonth()
    {   
        $date = \Carbon\Carbon::now();
        $pastMonth =  $date->subMonth()->format('m');

        $cost = Content::select(DB::raw("sum(tokens) as data, model",))->whereMonth('created_at', $pastMonth)->whereYear('created_at', $this->year)->groupBy('model')->get();
        $gpt_3t = ($cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first())/1000 * $this->cost->gpt_3t;
        $gpt_4t = ($cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first())/1000 * $this->cost->gpt_4t;
        $gpt_4 = ($cost->where('model', 'gpt-4')->pluck('data')->first())/1000 * $this->cost->gpt_4;
        $gpt_4o = ($cost->where('model', 'gpt-4o')->pluck('data')->first())/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini = ($cost->where('model', 'gpt-4o-mini')->pluck('data')->first())/1000 * $this->cost->gpt_4o_mini;
        $opus = ($cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first())/1000 * $this->cost->claude_3_opus;
        $sonnet = ($cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first())/1000 * $this->cost->claude_3_sonnet;
        $haiku = ($cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first())/1000 * $this->cost->claude_3_haiku;
        $gemini = ($cost->where('model', 'gemini_pro')->pluck('data')->first())/1000 * $this->cost->gemini_pro;           

        $templates = $gpt_3t + $gpt_4t + $gpt_4 + $gpt_4o + $gpt_4o_mini + $opus + $sonnet + $haiku + $gemini;

        $cost = ChatHistory::select(DB::raw("sum(words) as data, model"))->whereMonth('created_at', $pastMonth)->whereYear('created_at', $this->year)->groupBy('model')->get();           
        $gpt_3t = ($cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first())/1000 * $this->cost->gpt_3t;
        $gpt_4t = ($cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first())/1000 * $this->cost->gpt_4t;
        $gpt_4 = ($cost->where('model', 'gpt-4')->pluck('data')->first())/1000 * $this->cost->gpt_4;
        $gpt_4o = ($cost->where('model', 'gpt-4o')->pluck('data')->first())/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini = ($cost->where('model', 'gpt-4o-mini')->pluck('data')->first())/1000 * $this->cost->gpt_4o_mini;
        $opus = ($cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first())/1000 * $this->cost->claude_3_opus;
        $sonnet = ($cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first())/1000 * $this->cost->claude_3_sonnet;
        $haiku = ($cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first())/1000 * $this->cost->claude_3_haiku;
        $gemini = ($cost->where('model', 'gemini_pro')->pluck('data')->first())/1000 * $this->cost->gemini_pro;           

        $chats = $gpt_3t + $gpt_4t + $gpt_4 + $gpt_4o + $gpt_4o_mini + $opus + $sonnet + $haiku + $gemini;
 
        $de_images = Image::select(DB::raw("count(id) as data, vendor_engine"))->where('vendor', 'dalle')->whereMonth('created_at', $pastMonth)->whereYear('created_at', $this->year)->groupBy('vendor_engine')->get();
        $dalle_2 = ($de_images->where('vendor_engine', 'dall-e-2'))->pluck('data')->first() * $this->cost->dalle_2;
        $dalle_3 = ($de_images->where('vendor_engine', 'dall-e-3'))->pluck('data')->first() * $this->cost->dalle_3;
        $dalle_3_hd = ($de_images->where('vendor_engine', 'dall-e-3-hd'))->pluck('data')->first() * $this->cost->dalle_3_hd;
        $dalle = $dalle_2 + $dalle_3 + $dalle_3_hd;

        $sd_images = Image::select(DB::raw("sum(cost) as data"))->where('vendor', 'sd')->whereMonth('created_at', $pastMonth)->whereYear('created_at', $this->year)->get();
        $sd = ($sd_images->pluck('data')->first()/1000) * $this->cost->sd;

        return $chats + $templates + $dalle + $sd;
    }


    public function getSpendings()
    {
        $data = [];

        for($i = 1; $i <= 12; $i++) {
            $data[$i] = 0;
        }

        $results = Content::select(DB::raw("sum(tokens) as data"), DB::raw("MONTH(created_at) as month"), 'model')->groupBy('month', 'model')->orderBy('month')->get();
        $gpt_3t = $results->where('model', 'gpt-3.5-turbo-0125')->toArray();
        $gpt_4t = $results->where('model', 'gpt-4-0125-preview')->toArray();
        $gpt_4 = $results->where('model', 'gpt-4')->toArray();
        $gpt_4o = $results->where('model', 'gpt-4o')->toArray();
        $gpt_4o_mini = $results->where('model', 'gpt-4o-mini')->toArray();
        $opus = $results->where('model', 'claude-3-opus-20240229')->toArray();
        $sonnet = $results->where('model', 'claude-3-5-sonnet-20240620')->toArray();
        $haiku = $results->where('model', 'claude-3-haiku-20240307')->toArray();
        $gemini = $results->where('model', 'gemini_pro')->toArray();

        foreach ( $gpt_3t as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gpt_3t; }
        foreach ( $gpt_4t as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gpt_4t; }
        foreach ( $gpt_4 as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gpt_4; }
        foreach ( $gpt_4o as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gpt_4o; }
        foreach ( $gpt_4o_mini as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gpt_4o_mini; }
        foreach ( $opus as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->opus; }
        foreach ( $sonnet as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->sonnet; }
        foreach ( $haiku as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->haiku; }
        foreach ( $gemini as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gemini; }

        $chats = ChatHistory::select(DB::raw("sum(words) as data"), DB::raw("MONTH(created_at) as month"), 'model')->groupBy('month', 'model')->orderBy('month')->get();
        $gpt_3t = $chats->where('model', 'gpt-3.5-turbo-0125')->toArray();
        $gpt_4t = $chats->where('model', 'gpt-4-0125-preview')->toArray();
        $gpt_4 = $chats->where('model', 'gpt-4')->toArray();
        $gpt_4o = $chats->where('model', 'gpt-4o')->toArray();
        $gpt_4o_mini = $chats->where('model', 'gpt-4o-mini')->toArray();
        $opus = $chats->where('model', 'claude-3-opus-20240229')->toArray();
        $sonnet = $chats->where('model', 'claude-3-5-sonnet-20240620')->toArray();
        $haiku = $chats->where('model', 'claude-3-haiku-20240307')->toArray();
        $gemini = $chats->where('model', 'gemini_pro')->toArray();

        foreach ( $gpt_3t as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gpt_3t; }
        foreach ( $gpt_4t as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gpt_4t; }
        foreach ( $gpt_4 as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gpt_4; }
        foreach ( $gpt_4o as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gpt_4o; }
        foreach ( $gpt_4o_mini as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gpt_4o_mini; }
        foreach ( $opus as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->claude_3_opus; }
        foreach ( $sonnet as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->claude_3_sonnet; }
        foreach ( $haiku as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->claude_3_haiku; }
        foreach ( $gemini as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->gemini_pro; }

        $de_images = Image::select(DB::raw("count(id) as data"), DB::raw("MONTH(created_at) as month", 'vendor_engine'))->where('vendor', 'dalle')->groupBy('month', 'vendor_engine')->get();
        $dalle_2 = $de_images->where('vendor_engine', 'dall-e-2')->toArray();
        $dalle_3 = $de_images->where('vendor_engine', 'dall-e-3')->toArray();
        $dalle_3_hd = $de_images->where('vendor_engine', 'dall-e-3-hd')->toArray();
        foreach ( $dalle_2 as $row) { $month = $row['month']; $data[$month] += $row['data'] * $this->cost->dalle_2; }
        foreach ( $dalle_3 as $row) { $month = $row['month']; $data[$month] += $row['data'] * $this->cost->dalle_3; }
        foreach ( $dalle_3_hd as $row) { $month = $row['month']; $data[$month] += $row['data'] * $this->cost->dalle_3_hd; }

        $sd_images = Image::select(DB::raw("sum(cost) as data"), DB::raw("MONTH(created_at) as month"))->where('vendor', 'sd')->groupBy('month')->get();
        foreach ( $sd_images as $row) { $month = $row['month']; $data[$month] += ($row['data']/1000) * $this->cost->sd; }

        return $data;
    }


    public function getCosts() {
        $data = [];
        $data['OpenAI'] = 0;
        $data['Anthropic'] = 0;
        $data['Google'] = 0;
        $data['Stable Diffusion'] = 0;
        $data['AWS'] = 0;
        $data['Elevenlabs'] = 0;
        $data['Azure'] = 0;

        $cost = Content::select(DB::raw("sum(tokens) as data, model"))->groupBy('model')->get();  
        $gpt_3t = ($cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first())/1000 * $this->cost->gpt_3t;
        $gpt_4t = ($cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first())/1000 * $this->cost->gpt_4t;
        $gpt_4 = ($cost->where('model', 'gpt-4')->pluck('data')->first())/1000 * $this->cost->gpt_4;
        $gpt_4o = ($cost->where('model', 'gpt-4o')->pluck('data')->first())/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini = ($cost->where('model', 'gpt-4o-mini')->pluck('data')->first())/1000 * $this->cost->gpt_4o_mini;
        $opus = ($cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first())/1000 * $this->cost->claude_3_opus;
        $sonnet = ($cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first())/1000 * $this->cost->claude_3_sonnet;
        $haiku = ($cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first())/1000 * $this->cost->claude_3_haiku;
        $gemini = ($cost->where('model', 'gemini_pro')->pluck('data')->first())/1000 * $this->cost->gemini_pro;

        $data['OpenAI'] += $gpt_3t + $gpt_4t + $gpt_4 + $gpt_4o + $gpt_4o_mini;
        $data['Anthropic'] += $opus + $sonnet + $haiku;
        $data['Google'] += $gemini;

        $cost = ChatHistory::select(DB::raw("sum(words) as data, model"))->groupBy('model')->get();  
        $gpt_3t = ($cost->where('model', 'gpt-3.5-turbo-0125')->pluck('data')->first())/1000 * $this->cost->gpt_3t;
        $gpt_4t = ($cost->where('model', 'gpt-4-0125-preview')->pluck('data')->first())/1000 * $this->cost->gpt_4t;
        $gpt_4 = ($cost->where('model', 'gpt-4')->pluck('data')->first())/1000 * $this->cost->gpt_4;
        $gpt_4o = ($cost->where('model', 'gpt-4o')->pluck('data')->first())/1000 * $this->cost->gpt_4o;
        $gpt_4o_mini = ($cost->where('model', 'gpt-4o-mini')->pluck('data')->first())/1000 * $this->cost->gpt_4o_mini;
        $opus = ($cost->where('model', 'claude-3-opus-20240229')->pluck('data')->first())/1000 * $this->cost->claude_3_opus;
        $sonnet = ($cost->where('model', 'claude-3-5-sonnet-20240620')->pluck('data')->first())/1000 * $this->cost->claude_3_sonnet;
        $haiku = ($cost->where('model', 'claude-3-haiku-20240307')->pluck('data')->first())/1000 * $this->cost->claude_3_haiku;
        $gemini = ($cost->where('model', 'gemini_pro')->pluck('data')->first())/1000 * $this->cost->gemini_pro;

        $de_images = Image::select(DB::raw("count(id) as data, vendor_engine"))->where('vendor', 'dalle')->groupBy('vendor_engine')->get();
        $dalle_2 = ($de_images->where('vendor_engine', 'dall-e-2'))->pluck('data')->first() * $this->cost->dalle_2;
        $dalle_3 = ($de_images->where('vendor_engine', 'dall-e-3'))->pluck('data')->first() * $this->cost->dalle_3;
        $dalle_3_hd = ($de_images->where('vendor_engine', 'dall-e-3-hd'))->pluck('data')->first() * $this->cost->dalle_3_hd;
        $dalle = $dalle_2 + $dalle_3 + $dalle_3_hd;

        $sd_images = Image::select(DB::raw("sum(cost) as data"))->where('vendor', 'sd')->get();
        $sd = ($sd_images->pluck('data')->first()/1000) * $this->cost->sd;

        $data['OpenAI'] += $gpt_3t + $gpt_4t + $gpt_4 + $gpt_4o + $gpt_4o_mini + $dalle;
        $data['Anthropic'] += $opus + $sonnet + $haiku;
        $data['Google'] += $gemini;
        $data['Stable Diffusion'] = $sd;

        return $data;
    }

}