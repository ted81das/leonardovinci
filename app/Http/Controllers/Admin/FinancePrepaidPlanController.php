<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use Illuminate\Http\Request;
use App\Models\PrepaidPlan;
use App\Models\VendorPrice;
use DataTables;
use Carbon\Carbon;

class FinancePrepaidPlanController extends Controller
{
    private $api;

    public function __construct()
    {
        $this->api = new LicenseController();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {        
        if ($request->ajax()) {
            $data = PrepaidPlan::all()->sortByDesc("created_at");          
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('actions', function($row){
                        $actionBtn = '<div>                                            
                                        <a href="'. route("admin.finance.prepaid.show", $row["id"] ). '"><i class="fa-solid fa-file-invoice-dollar table-action-buttons edit-action-button" title="'. __('View Plan') .'"></i></a>
                                        <a href="'. route("admin.finance.prepaid.edit", $row["id"] ). '"><i class="fa-solid fa-file-pen table-action-buttons view-action-button" title="'. __('Update Plan') .'"></i></a>
                                        <a class="deletePlanButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete Plan') .'"></i></a>
                                    </div>';
                        return $actionBtn;
                    })
                    ->addColumn('created-on', function($row){
                        $created_on = '<span class="text-muted">'.date_format($row["created_at"], 'M d, Y').'</span>';
                        return $created_on;
                    })
                    ->addColumn('custom-status', function($row){
                        $custom_priority = '<span class="cell-box plan-'.strtolower($row["status"]).'">'.__(ucfirst($row["status"])).'</span>';
                        return $custom_priority;
                    })
                    ->addColumn('custom-name', function($row){
                        $custom_name = '<span class="font-weight-bold">'.$row["plan_name"].'</span>';
                        return $custom_name;
                    })
                    ->addColumn('custom-price', function($row){
                        $custom_name = '<span class="text-muted">' . $row["price"] . ' ' . $row["currency"].'</span>';
                        return $custom_name;
                    })
                    ->addColumn('custom-featured', function($row){
                        $icon = ($row['featured'] == true) ? '<i class="fa-solid fa-circle-check text-success fs-16"></i>' : '<i class="fa-solid fa-circle-xmark fs-16"></i>';
                        $custom_featured = '<span class="font-weight-bold">'.$icon.'</span>';
                        return $custom_featured;
                    })
                    ->addColumn('custom-frequency', function($row){
                        $custom_status = '<span class="cell-box payment-prepaid">'.__(ucfirst($row["pricing_plan"])).'</span>';
                        return $custom_status;
                    })
                    ->rawColumns(['actions', 'custom-status', 'created-on', 'custom-name', 'custom-featured', 'custom-frequency', 'custom-price'])
                    ->make(true);
                    
        }

        $verify = $this->api->verify_license();
        $type = (isset($verify['type'])) ? $verify['type'] : '';
        $status = (Carbon::parse($verify['date'])->gte(Carbon::parse('2024-07-01'))) ? true : false;

        return view('admin.finance.plans.prepaid.index', compact('type', 'status'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $verify = $this->api->verify_license();
        $type = (isset($verify['type'])) ? $verify['type'] : '';
        $status = (Carbon::parse($verify['date'])->gte(Carbon::parse('2024-07-01'))) ? true : false;
        $prices = VendorPrice::first();

        return view('admin.finance.plans.prepaid.create', compact('type', 'status', 'prices'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        request()->validate([
            'plan-status' => 'required',
            'plan-name' => 'required',
            'price' => 'required|numeric',
            'currency' => 'required',
        ]);
        
        $frequency = 'prepaid';

        $plan = new PrepaidPlan([
            'status' => request('plan-status'),
            'plan_name' => request('plan-name'),
            'price' => request('price'),
            'currency' => request('currency'),
            'pricing_plan' => $frequency,
            'featured' => request('featured'),
            'gpt_3_turbo_credits_prepaid' => request('gpt_3_turbo'),
            'gpt_4_turbo_credits_prepaid' => request('gpt_4_turbo'),
            'gpt_4_credits_prepaid' => request('gpt_4'),
            'gpt_4o_credits_prepaid' => request('gpt_4o'),
            'gpt_4o_mini_credits_prepaid' => request('gpt_4o_mini'),
            'o1_mini_credits_prepaid' => request('o1_mini'),
            'o1_preview_credits_prepaid' => request('o1_preview'),
            'claude_3_opus_credits_prepaid' => request('claude_3_opus'),
            'claude_3_sonnet_credits_prepaid' => request('claude_3_sonnet'),
            'claude_3_haiku_credits_prepaid' => request('claude_3_haiku'),
            'gemini_pro_credits_prepaid' => request('gemini_pro'),
            'fine_tune_credits_prepaid' => request('fine_tune'),
            'dalle_images' => request('dalle_images'),
            'sd_images' => request('sd_images'),
            'characters' => request('characters'),
            'minutes' => request('minutes'),
        ]); 
            
        $plan->save();            

        toastr()->success(__('New prepaid plan has been created successfully'));
        return redirect()->route('admin.finance.prepaid');        
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(PrepaidPlan $id)
    {
        $verify = $this->api->verify_license();
        $type = (isset($verify['type'])) ? $verify['type'] : '';
        $status = (Carbon::parse($verify['date'])->gte(Carbon::parse('2024-07-01'))) ? true : false;

        return view('admin.finance.plans.prepaid.show', compact('id', 'type', 'status'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(PrepaidPlan $id)
    {
        $verify = $this->api->verify_license();
        $type = (isset($verify['type'])) ? $verify['type'] : '';
        $status = (Carbon::parse($verify['date'])->gte(Carbon::parse('2024-07-01'))) ? true : false;
        $prices = VendorPrice::first();

        return view('admin.finance.plans.prepaid.edit', compact('id', 'type', 'status', 'prices'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(PrepaidPlan $id)
    {        
        request()->validate([
            'plan-status' => 'required',
            'plan-name' => 'required',
            'price' => 'required|numeric',
            'currency' => 'required',
        ]);

        $id->update([
            'status' => request('plan-status'),
            'plan_name' => request('plan-name'),
            'price' => request('price'),
            'currency' => request('currency'),
            'gpt_3_turbo_credits_prepaid' => request('gpt_3_turbo'),
            'gpt_4_turbo_credits_prepaid' => request('gpt_4_turbo'),
            'gpt_4_credits_prepaid' => request('gpt_4'),
            'gpt_4o_credits_prepaid' => request('gpt_4o'),
            'gpt_4o_mini_credits_prepaid' => request('gpt_4o_mini'),
            'o1_mini_credits_prepaid' => request('o1_mini'),
            'o1_preview_credits_prepaid' => request('o1_preview'),
            'claude_3_opus_credits_prepaid' => request('claude_3_opus'),
            'claude_3_sonnet_credits_prepaid' => request('claude_3_sonnet'),
            'claude_3_haiku_credits_prepaid' => request('claude_3_haiku'),
            'gemini_pro_credits_prepaid' => request('gemini_pro'),
            'fine_tune_credits_prepaid' => request('fine_tune'),
            'dalle_images' => request('dalle_images'),
            'sd_images' => request('sd_images'),
            'characters' => request('characters'),
            'minutes' => request('minutes'),
            'featured' => request('featured'),
        ]); 

        toastr()->success(__('Selected prepaid plan has been updated successfully'));
        return redirect()->route('admin.finance.prepaid');

    }


    public function delete(Request $request)
    {   
        if ($request->ajax()) {

            $plan = PrepaidPlan::find(request('id'));

            if($plan) {

                $plan->delete();

                return response()->json('success');

            } else{
                return response()->json('error');
            } 
        } 
    }
}
