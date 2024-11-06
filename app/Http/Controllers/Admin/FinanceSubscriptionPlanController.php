<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\Subscriber;
use App\Models\FineTuneModel;
use App\Models\VendorPrice;
use App\Models\User;
use Carbon\Carbon;
use DataTables;
use Exception;
use DB;

class FinanceSubscriptionPlanController extends Controller
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
            $data = SubscriptionPlan::all()->sortByDesc("created_at");          
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('actions', function($row){
                        $actionBtn = '<div>                                            
                                            <a href="'. route("admin.finance.plan.show", $row["id"] ). '"><i class="fa-solid fa-file-invoice-dollar table-action-buttons edit-action-button" title="'. __('View Plan') .'"></i></a>
                                            <a href="'. route("admin.finance.plan.edit", $row["id"] ). '"><i class="fa-solid fa-file-pen table-action-buttons view-action-button" title="'. __('Update Plan') .'"></i></a>
                                            <a href="'. route("admin.finance.plan.renew", $row["id"] ). '"><i class="fa-solid fa-box-check table-action-buttons view-action-button" title="'. __('Renew Credits') .'"></i></a>
                                            <a class="deletePlanButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete Plan') .'"></i></a>
                                        </div>';
                        return $actionBtn;
                    })
                    ->addColumn('created-on', function($row){
                        $created_on = '<span class="text-muted">'.date_format($row["created_at"], 'M d, Y').'</span>';
                        return $created_on;
                    })
                    ->addColumn('custom-status', function($row){
                        $custom_priority = '<span class="cell-box plan-'.strtolower($row["status"]).'">'.ucfirst($row["status"]).'</span>';
                        return $custom_priority;
                    })
                    ->addColumn('frequency', function($row){
                        $custom_status = '<span class="cell-box payment-'.strtolower($row["payment_frequency"]).'">'.ucfirst($row["payment_frequency"]).'</span>';
                        return $custom_status;
                    })
                    ->addColumn('custom-subscribers', function($row){
                        $value = $this->countSubscribers($row['id']);
                        $custom_storage = '<span class="text-muted">'.$value.'</span>';
                        return $custom_storage;
                    })
                    ->addColumn('custom-name', function($row){
                        $custom_name = '<span class="font-weight-bold">'.$row["plan_name"].'</span>';
                        return $custom_name;
                    })
                    ->addColumn('custom-price', function($row){
                        $custom_name = '<span class="text-muted">'.$row["price"] . ' ' . $row["currency"].'</span>';
                        return $custom_name;
                    })
                    ->addColumn('custom-featured', function($row){
                        $icon = ($row['featured'] == true) ? '<i class="fa-solid fa-circle-check text-success fs-16"></i>' : '<i class="fa-solid fa-circle-xmark fs-16"></i>';
                        $custom_featured = '<span class="font-weight-bold">'.$icon.'</span>';
                        return $custom_featured;
                    })
                    ->addColumn('custom-free', function($row){
                        $icon = ($row['free'] == true) ? '<i class="fa-solid fa-circle-check text-success fs-16"></i>' : '<i class="fa-solid fa-circle-xmark fs-16"></i>';
                        $custom_featured = '<span class="font-weight-bold">'.$icon.'</span>';
                        return $custom_featured;
                    })
                    ->rawColumns(['actions', 'custom-status', 'created-on', 'custom-subscribers', 'frequency', 'custom-name', 'custom-featured', 'custom-free', 'custom-price'])
                    ->make(true);
                    
        }

        $verify = $this->api->verify_license();
        $type = (isset($verify['type'])) ? $verify['type'] : '';
        $status = (Carbon::parse($verify['date'])->gte(Carbon::parse('2024-07-01'))) ? true : false;

        return view('admin.finance.plans.index', compact('type', 'status'));
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

        $models = FineTuneModel::all();
        $prices = VendorPrice::first();

        return view('admin.finance.plans.create', compact('models', 'type', 'status', 'prices'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'plan-status' => 'required',
            'plan-name' => 'required',
            'cost' => 'required|numeric',
            'currency' => 'required',
            'frequency' => 'required',
        ]);

        if (request('writer-feature') == 'on') {
            $writer = true; 
        } else {
            $writer = false;
        }

        if (request('voiceover-feature') == 'on') {
            $voiceover = true; 
        } else {
            $voiceover = false;
        }

        if (request('image-feature') == 'on') {
            $image = true; 
        } else {
            $image = false;
        }

        if (request('whisper-feature') == 'on') {
            $whisper = true; 
        } else {
            $whisper = false;
        }

        if (request('chat-feature') == 'on') {
            $chat = true; 
        } else {
            $chat = false;
        }

        if (request('code-feature') == 'on') {
            $code = true; 
        } else {
            $code = false;
        }

        if (request('personal-openai-api') == 'on') {
            $openai_personal = true; 
        } else {
            $openai_personal = false;
        }

        if (request('personal-claude-api') == 'on') {
            $claude_personal = true; 
        } else {
            $claude_personal = false;
        }

        if (request('personal-gemini-api') == 'on') {
            $gemini_personal = true; 
        } else {
            $gemini_personal = false;
        }

        if (request('personal-sd-api') == 'on') {
            $sd_personal = true; 
        } else {
            $sd_personal = false;
        }

        if (request('wizard-feature') == 'on') {
            $wizard = true; 
        } else {
            $wizard = false;
        }

        if (request('vision-feature') == 'on') {
            $vision = true; 
        } else {
            $vision = false;
        }

        if (request('chat-image-feature') == 'on') {
            $chat_image = true; 
        } else {
            $chat_image = false;
        }

        if (request('file-chat-feature') == 'on') {
            $file = true; 
        } else {
            $file = false;
        }

        if (request('internet-feature') == 'on') {
            $internet = true; 
        } else {
            $internet = false;
        }

        if (request('chat-web-feature') == 'on') {
            $web = true; 
        } else {
            $web = false;
        }

        if (request('smart-editor-feature') == 'on') {
            $smart = true; 
        } else {
            $smart = false;
        }

        if (request('rewriter-feature') == 'on') {
            $rewriter = true; 
        } else {
            $rewriter = false;
        }

        if (request('video-image-feature') == 'on') {
            $video_image = true; 
        } else {
            $video_image = false;
        }

        if (request('photo-studio-feature') == 'on') {
            $photo_studio = true; 
        } else {
            $photo_studio = false;
        }

        if (request('voice-clone-feature') == 'on') {
            $clone = true; 
        } else {
            $clone = false;
        }

        if (request('sound-studio-feature') == 'on') {
            $studio = true; 
        } else {
            $studio = false;
        }

        if (request('plagiarism-feature') == 'on') {
            $plagiarism = true; 
        } else {
            $plagiarism = false;
        }

        if (request('detector-feature') == 'on') {
            $detector = true; 
        } else {
            $detector = false;
        }

        if (request('personal-chat-feature') == 'on') {
            $personal_chat = true; 
        } else {
            $personal_chat = false;
        }

        if (request('personal-template-feature') == 'on') {
            $personal_template = true; 
        } else {
            $personal_template = false;
        }

        if (request('brand-voice-feature') == 'on') {
            $brand_voice = true; 
        } else {
            $brand_voice = false;
        }

        if (request('integration-feature') == 'on') {
            $integration = true; 
        } else {
            $integration = false;
        }

        if (request('youtube-feature') == 'on') {
            $youtube = true; 
        } else {
            $youtube = false;
        }

        if (request('rss-feature') == 'on') {
            $rss = true; 
        } else {
            $rss = false;
        }

        $voiceover_vendors = '';
        if (!is_null(request('voiceover_vendors'))) {
            foreach (request('voiceover_vendors') as $key => $value) {
                if ($key === array_key_last(request('voiceover_vendors'))) {
                    $voiceover_vendors .= $value; 
                } else {
                    $voiceover_vendors .= $value . ', '; 
                }                
            }
        }

        $template_models = '';
        if (!is_null(request('templates_models_list'))) {
            foreach (request('templates_models_list') as $key => $value) {
                if ($key === array_key_last(request('templates_models_list'))) {
                    $template_models .= $value; 
                } else {
                    $template_models .= $value . ', '; 
                }   
            }
        }

        $chat_models = '';
        if (!is_null(request('chats_models_list'))) {
            foreach (request('chats_models_list') as $key => $value) {
                if ($key === array_key_last(request('chats_models_list'))) {
                    $chat_models .= $value; 
                } else {
                    $chat_models .= $value . ', '; 
                }   
            }
        }

        try {
            $plan = new SubscriptionPlan([
                'paypal_gateway_plan_id' => request('paypal_gateway_plan_id'),
                'stripe_gateway_plan_id' => request('stripe_gateway_plan_id'),
                'paystack_gateway_plan_id' => request('paystack_gateway_plan_id'),
                'razorpay_gateway_plan_id' => request('razorpay_gateway_plan_id'),
                'flutterwave_gateway_plan_id' => request('flutterwave_gateway_plan_id'),
                'paddle_gateway_plan_id' => request('paddle_gateway_plan_id'),
                'status' => request('plan-status'),
                'plan_name' => request('plan-name'),
                'price' => request('cost'),
                'currency' => request('currency'),
                'free' => request('free-plan'),
                'image_feature' => $image,
                'voiceover_feature' => $voiceover,
                'transcribe_feature' => $whisper,
                'chat_feature' => $chat,
                'code_feature' => $code,
                'templates' => request('templates'),
                'chats' => request('chats'),
                'characters' => request('characters'),
                'minutes' => request('minutes'),
                'payment_frequency' => request('frequency'),
                'primary_heading' => request('primary-heading'),
                'featured' => request('featured'),
                'plan_features' => request('features'),
                'max_tokens' => request('tokens'),
                'model' => $template_models,
                'model_chat' => $chat_models,
                'team_members' => request('team-members'),
                'personal_openai_api' => $openai_personal,
                'personal_claude_api' => $claude_personal,
                'personal_gemini_api' => $gemini_personal,
                'personal_sd_api' => $sd_personal,
                'days' => request('days'),
                'dalle_image_engine' => request('dalle-image-engine'),
                'sd_image_engine' => request('sd-image-engine'),
                'wizard_feature' => $wizard,
                'writer_feature' => $writer,
                'vision_feature' => $vision,
                'internet_feature' => $internet,
                'chat_image_feature' => $chat_image,
                'file_chat_feature' => $file,
                'chat_web_feature' => $web,
                'chat_csv_file_size' => request('chat-csv-file-size'),
                'chat_pdf_file_size' => request('chat-pdf-file-size'),
                'chat_word_file_size' => request('chat-word-file-size'),
                'voice_clone_number' => request('voice_clone_number'),
                'rewriter_feature' => $rewriter,
                'smart_editor_feature' => $smart,
                'video_image_feature' => $video_image,
                'photo_studio_feature' => $photo_studio,
                'voice_clone_feature' => $clone,
                'sound_studio_feature' => $studio,
                'plagiarism_feature' => $plagiarism,
                'ai_detector_feature' => $detector,
                'plagiarism_pages' => request('plagiarism-pages'),
                'ai_detector_pages' => request('detector-pages'),
                'personal_chats_feature' => $personal_chat,
                'personal_templates_feature' => $personal_template,
                'voiceover_vendors' => $voiceover_vendors,
                'brand_voice_feature' => $brand_voice,
                'file_result_duration' => request('file-result-duration'),
                'document_result_duration' => request('document-result-duration'),
                'dalle_images' => request('dalle-images'),
                'sd_images' => request('sd-images'),
                'gpt_3_turbo_credits' => request('gpt_3_turbo'),
                'gpt_4_turbo_credits' => request('gpt_4_turbo'),
                'gpt_4_credits' => request('gpt_4'),
                'gpt_4o_credits' => request('gpt_4o'),
                'gpt_4o_mini_credits' => request('gpt_4o_mini'),
                'o1_mini_credits' => request('o1_mini'),
                'o1_preview_credits' => request('o1_preview'),
                'claude_3_opus_credits' => request('claude_3_opus'),
                'claude_3_sonnet_credits' => request('claude_3_sonnet'),
                'claude_3_haiku_credits' => request('claude_3_haiku'),
                'fine_tune_credits' => request('fine_tune'),
                'gemini_pro_credits' => request('gemini_pro'),
                'integration_feature' => $integration,
                'youtube_feature' => $youtube,
                'rss_feature' => $rss,
            ]); 
                   
            $plan->save();            
    
            toastr()->success(__('New subscription plan has been created successfully'));
            return redirect()->route('admin.finance.plans');

        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return redirect()->back();
        }

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(SubscriptionPlan $id)
    {
        $verify = $this->api->verify_license();
        $type = (isset($verify['type'])) ? $verify['type'] : '';
        $status = (Carbon::parse($verify['date'])->gte(Carbon::parse('2024-07-01'))) ? true : false;

        return view('admin.finance.plans.show', compact('id', 'type', 'status'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(SubscriptionPlan $id)
    {
        $models = FineTuneModel::all();
        $vendors = explode(',', $id->voiceover_vendors);
        $model_templates = explode(',', $id->model);
        $model_chats = explode(',', $id->model_chat);

        $verify = $this->api->verify_license();
        $type = (isset($verify['type'])) ? $verify['type'] : '';
        $status = (Carbon::parse($verify['date'])->gte(Carbon::parse('2024-07-01'))) ? true : false;
        $prices = VendorPrice::first();

        return view('admin.finance.plans.edit', compact('id', 'models', 'vendors', 'model_templates', 'model_chats', 'type', 'status', 'prices'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SubscriptionPlan $id)
    {
        request()->validate([
            'plan-status' => 'required',
            'plan-name' => 'required',
            'cost' => 'required|numeric',
            'currency' => 'required',
            'frequency' => 'required',
        ]);

        if (request('writer-feature') == 'on') {
            $writer = true; 
        } else {
            $writer = false;
        }

        if (request('voiceover-feature') == 'on') {
            $voiceover = true; 
        } else {
            $voiceover = false;
        }

        if (request('image-feature') == 'on') {
            $image = true; 
        } else {
            $image = false;
        }

        if (request('whisper-feature') == 'on') {
            $whisper = true; 
        } else {
            $whisper = false;
        }

        if (request('chat-feature') == 'on') {
            $chat = true; 
        } else {
            $chat = false;
        }

        if (request('code-feature') == 'on') {
            $code = true; 
        } else {
            $code = false;
        }

        if (request('personal-openai-api') == 'on') {
            $openai_personal = true; 
        } else {
            $openai_personal = false;
        }

        if (request('personal-claude-api') == 'on') {
            $claude_personal = true; 
        } else {
            $claude_personal = false;
        }

        if (request('personal-gemini-api') == 'on') {
            $gemini_personal = true; 
        } else {
            $gemini_personal = false;
        }

        if (request('personal-sd-api') == 'on') {
            $sd_personal = true; 
        } else {
            $sd_personal = false;
        }

        if (request('wizard-feature') == 'on') {
            $wizard = true; 
        } else {
            $wizard = false;
        }

        if (request('vision-feature') == 'on') {
            $vision = true; 
        } else {
            $vision = false;
        }

        if (request('chat-image-feature') == 'on') {
            $chat_image = true; 
        } else {
            $chat_image = false;
        }

        if (request('file-chat-feature') == 'on') {
            $file = true; 
        } else {
            $file = false;
        }

        if (request('internet-feature') == 'on') {
            $internet = true; 
        } else {
            $internet = false;
        }

        if (request('chat-web-feature') == 'on') {
            $web = true; 
        } else {
            $web = false;
        }

        if (request('smart-editor-feature') == 'on') {
            $smart = true; 
        } else {
            $smart = false;
        }

        if (request('rewriter-feature') == 'on') {
            $rewriter = true; 
        } else {
            $rewriter = false;
        }

        if (request('video-image-feature') == 'on') {
            $video_image = true; 
        } else {
            $video_image = false;
        }

        if (request('photo-studio-feature') == 'on') {
            $photo_studio = true; 
        } else {
            $photo_studio = false;
        }

        if (request('voice-clone-feature') == 'on') {
            $clone = true; 
        } else {
            $clone = false;
        }

        if (request('sound-studio-feature') == 'on') {
            $studio = true; 
        } else {
            $studio = false;
        }

        if (request('plagiarism-feature') == 'on') {
            $plagiarism = true; 
        } else {
            $plagiarism = false;
        }

        if (request('detector-feature') == 'on') {
            $detector = true; 
        } else {
            $detector = false;
        }

        if (request('personal-chat-feature') == 'on') {
            $personal_chat = true; 
        } else {
            $personal_chat = false;
        }

        if (request('personal-template-feature') == 'on') {
            $personal_template = true; 
        } else {
            $personal_template = false;
        }

        if (request('brand-voice-feature') == 'on') {
            $brand_voice = true; 
        } else {
            $brand_voice = false;
        }

        if (request('integration-feature') == 'on') {
            $integration = true; 
        } else {
            $integration = false;
        }

        if (request('youtube-feature') == 'on') {
            $youtube = true; 
        } else {
            $youtube = false;
        }

        if (request('rss-feature') == 'on') {
            $rss = true; 
        } else {
            $rss = false;
        }

        $voiceover_vendors = '';
        if (!is_null(request('voiceover_vendors'))) {
            foreach (request('voiceover_vendors') as $key => $value) {
                if ($key === array_key_last(request('voiceover_vendors'))) {
                    $voiceover_vendors .= $value; 
                } else {
                    $voiceover_vendors .= $value . ', '; 
                }                
            }
        }

        $template_models = '';
        if (!is_null(request('templates_models_list'))) {
            foreach (request('templates_models_list') as $key => $value) {
                if ($key === array_key_last(request('templates_models_list'))) {
                    $template_models .= $value; 
                } else {
                    $template_models .= $value . ', '; 
                }   
            }
        }

        $chat_models = '';
        if (!is_null(request('chats_models_list'))) {
            foreach (request('chats_models_list') as $key => $value) {
                if ($key === array_key_last(request('chats_models_list'))) {
                    $chat_models .= $value; 
                } else {
                    $chat_models .= $value . ', '; 
                }   
            }
        }

        try {

            $id->update([
                'paypal_gateway_plan_id' => request('paypal_gateway_plan_id'),
                'stripe_gateway_plan_id' => request('stripe_gateway_plan_id'),
                'paystack_gateway_plan_id' => request('paystack_gateway_plan_id'),
                'razorpay_gateway_plan_id' => request('razorpay_gateway_plan_id'),
                'flutterwave_gateway_plan_id' => request('flutterwave_gateway_plan_id'),
                'paddle_gateway_plan_id' => request('paddle_gateway_plan_id'),
                'status' => request('plan-status'),
                'plan_name' => request('plan-name'),
                'price' => request('cost'),
                'currency' => request('currency'),
                'free' => request('free-plan'),
                'characters' => request('characters'),
                'minutes' => request('minutes'),
                'payment_frequency' => request('frequency'),
                'primary_heading' => request('primary-heading'),
                'featured' => request('featured'),
                'plan_features' => request('features'),
                'image_feature' => $image,
                'voiceover_feature' => $voiceover,
                'transcribe_feature' => $whisper,
                'chat_feature' => $chat,
                'code_feature' => $code,
                'templates' => request('templates'),
                'chats' => request('chats'),
                'max_tokens' => request('tokens'),
                'model' => $template_models,
                'model_chat' => $chat_models,
                'team_members' => request('team-members'),
                'personal_openai_api' => $openai_personal,
                'personal_claude_api' => $claude_personal,
                'personal_gemini_api' => $gemini_personal,
                'personal_sd_api' => $sd_personal,
                'days' => request('days'),
                'dalle_image_engine' => request('dalle-image-engine'),
                'sd_image_engine' => request('sd-image-engine'),
                'wizard_feature' => $wizard,
                'writer_feature' => $writer,
                'vision_feature' => $vision,
                'internet_feature' => $internet,
                'chat_image_feature' => $chat_image,
                'file_chat_feature' => $file,
                'chat_web_feature' => $web,
                'chat_csv_file_size' => request('chat-csv-file-size'),
                'chat_pdf_file_size' => request('chat-pdf-file-size'),
                'chat_word_file_size' => request('chat-word-file-size'),
                'voice_clone_number' => request('voice_clone_number'),
                'rewriter_feature' => $rewriter,
                'smart_editor_feature' => $smart,
                'video_image_feature' => $video_image,
                'photo_studio_feature' => $photo_studio,
                'voice_clone_feature' => $clone,
                'sound_studio_feature' => $studio,
                'plagiarism_feature' => $plagiarism,
                'ai_detector_feature' => $detector,
                'plagiarism_pages' => request('plagiarism-pages'),
                'ai_detector_pages' => request('detector-pages'),
                'personal_chats_feature' => $personal_chat,
                'personal_templates_feature' => $personal_template,
                'voiceover_vendors' => $voiceover_vendors,
                'brand_voice_feature' => $brand_voice,
                'file_result_duration' => request('file-result-duration'),
                'document_result_duration' => request('document-result-duration'),
                'dalle_images' => request('dalle-images'),
                'sd_images' => request('sd-images'),
                'gpt_3_turbo_credits' => request('gpt_3_turbo'),
                'gpt_4_turbo_credits' => request('gpt_4_turbo'),
                'gpt_4_credits' => request('gpt_4'),
                'gpt_4o_credits' => request('gpt_4o'),
                'gpt_4o_mini_credits' => request('gpt_4o_mini'),
                'o1_mini_credits' => request('o1_mini'),
                'o1_preview_credits' => request('o1_preview'),
                'claude_3_opus_credits' => request('claude_3_opus'),
                'claude_3_sonnet_credits' => request('claude_3_sonnet'),
                'claude_3_haiku_credits' => request('claude_3_haiku'),
                'fine_tune_credits' => request('fine_tune'),
                'gemini_pro_credits' => request('gemini_pro'),
                'integration_feature' => $integration,
                'youtube_feature' => $youtube,
                'rss_feature' => $rss,
            ]); 
            
            toastr()->success(__('Selected plan has been updated successfully'));
            return redirect()->route('admin.finance.plans');
            
        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return redirect()->back();
        }
    }

    
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        if ($request->ajax()) {

            $plan = SubscriptionPlan::find(request('id'));

            if($plan) {

                $plan->delete();

                return response()->json('success');

            } else{
                return response()->json('error');
            } 
        }
    }


    public function countSubscribers($id)
    {
        $total_voiceover = Subscriber::select(DB::raw("count(id) as data"))
                ->where('plan_id', $id)
                ->where('status', 'Active')
                ->get();  
        
        return $total_voiceover[0]['data'];
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function renew(SubscriptionPlan $id)
    {
        $subscribers = $this->countSubscribers($id->id);

        return view('admin.finance.plans.renew', compact('id', 'subscribers'));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function push(Request $request, SubscriptionPlan $id)
    {
        $subscribers = Subscriber::where('plan_id', $id->id)->where('status', 'Active')->get(); 

        foreach ($subscribers as $subscriber) {
            $user = User::where('id', $subscriber->user_id)->first();
            if ($user) {
                if (request('gpt_3_check') == 'on') {
                    $user->gpt_3_turbo_credits = request('gpt_3_turbo');
                }
    
                if (request('gpt_4_check') == 'on') {
                    $user->gpt_4_credits = request('gpt_4');
                }
    
                if (request('gpt_4o_check') == 'on') {
                    $user->gpt_4o_credits = request('gpt_4o');
                }
    
                if (request('gpt_4o_mini_check') == 'on') {
                    $user->gpt_4o_mini_credits = request('gpt_4o_mini');
                }
    
                if (request('gpt_4_turbo_check') == 'on') {
                    $user->gpt_4_turbo_credits = request('gpt_4_turbo');
                }

                if (request('o1_mini_check') == 'on') {
                    $user->o1_mini_credits = request('o1_mini');
                }

                if (request('o1_preview_check') == 'on') {
                    $user->o1_preview_credits = request('o1_preview');
                }
    
                if (request('claude_3_opus_check') == 'on') {
                    $user->claude_3_opus_credits = request('claude_3_opus');
                }
    
                if (request('claude_3_sonnet_check') == 'on') {
                    $user->claude_3_sonnet_credits = request('claude_3_sonnet');
                }
    
                if (request('claude_3_haiku_check') == 'on') {
                    $user->claude_3_haiku_credits = request('claude_3_haiku');
                }
    
                if (request('gemini_pro_check') == 'on') {
                    $user->gemini_pro_credits = request('gemini_pro');
                }
    
                if (request('fine_tune_check') == 'on') {
                    $user->fine_tune_credits = request('fine_tune');
                }
    
                if (request('dalle_check') == 'on') {
                    $user->available_dalle_images = request('dalle-images');
                }
    
                if (request('sd_check') == 'on') {
                    $user->available_sd_images = request('sd-images');
                }
    
                if (request('minutes_check') == 'on') {
                    $user->available_minutes = request('minutes');
                }
    
                if (request('characters_check') == 'on') {
                    $user->available_chars = request('characters');
                }
    
                $user->save();
            }
            
        }
        
       
        toastr()->success(__('Credits were applied to all active subscribers'));
        return redirect()->back();
    }
}
