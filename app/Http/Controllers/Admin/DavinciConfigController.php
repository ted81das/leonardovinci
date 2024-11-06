<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Service;
use Illuminate\Http\Request;
use App\Models\Language;
use App\Models\Vendor;
use App\Models\ApiKey;
use App\Models\Setting;
use App\Models\MainSetting;
use App\Models\SdCost;
use App\Models\FineTune;
use App\Models\FineTuneModel;
use App\Models\VendorPrice;
use Yajra\DataTables\DataTables;
use OpenAI\Laravel\Facades\OpenAI;
use Exception;
use DB;


class DavinciConfigController extends Controller
{
    /**
     * Display TTS configuration settings
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $api = new Service();
        $verify = $api->verify_license();
        $notification = $verify['status'];

        $languages = Language::orderBy('languages.language', 'asc')->get();
        $filters = Setting::where('name', 'words_filter')->first();

        # Set Voice Types
        $voiceover_languages = DB::table('voices')
            ->join('vendors', 'voices.vendor_id', '=', 'vendors.vendor_id')
            ->join('voiceover_languages', 'voices.language_code', '=', 'voiceover_languages.language_code')
            ->where('vendors.enabled', '1')
            ->where('voices.status', 'active')
            ->select('voiceover_languages.id', 'voiceover_languages.language', 'voices.language_code', 'voiceover_languages.language_flag')                
            ->distinct()
            ->orderBy('voiceover_languages.language', 'asc')
            ->get();

        $voices = DB::table('voices')
            ->join('vendors', 'voices.vendor_id', '=', 'vendors.vendor_id')
            ->where('vendors.enabled', '1')
            ->where('voices.status', 'active')
            ->orderBy('voices.voice_type', 'desc')
            ->orderBy('voices.voice', 'asc')
            ->get();
        
        
        $models = FineTuneModel::all();
        $type = (isset($verify['type'])) ? $verify['type'] : '';
        $vendors = explode(',', config('settings.voiceover_free_tier_vendors'));
        $all_models = explode(',', config('settings.free_tier_models'));

        return view('admin.davinci.configuration.index', compact('languages', 'voiceover_languages', 'voices', 'filters', 'models', 'notification', 'type', 'vendors', 'all_models'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->ajax()) {  

            $this->storeConfiguration('DAVINCI_SETTINGS_DEFAULT_STORAGE', request('default-storage'));
            $this->storeConfiguration('DAVINCI_SETTINGS_DEFAULT_MODEL_ADMIN', request('default-model-admin'));
            $this->storeConfiguration('DAVINCI_SETTINGS_DEFAULT_EMBEDDING_MODEL', request('default-embedding-model'));
            $this->storeConfiguration('DAVINCI_SETTINGS_DEFAULT_LANGUAGE', request('default-language'));
            $this->storeConfiguration('DAVINCI_SETTINGS_TEMPLATES_ACCESS_ADMIN', request('templates-admin'));
            $this->storeConfiguration('DAVINCI_SETTINGS_IMAGE_SERVICE_VENDOR', request('image-vendor'));
            $this->storeConfiguration('DAVINCI_SETTINGS_IMAGE_STABLE_DIFFUSION_ENGINE', request('stable-diffusion-engine'));
            $this->storeConfiguration('DAVINCI_SETTINGS_IMAGE_DALLE_ENGINE', request('dalle-engine'));
            $this->storeConfiguration('DAVINCI_SETTINGS_MAX_RESULTS_LIMIT_ADMIN', request('max-results-admin'));
            $this->storeConfiguration('DAVINCI_SETTINGS_WIZARD_IMAGE_VENDOR', request('wizard-image-vendor'));
            $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_DEFAULT_VOICE', request('chat-default-voice'));
            $this->storeConfiguration('DAVINCI_SETTINGS_CUSTOM_CHATS', request('custom-chats'));
            $this->storeConfiguration('DAVINCI_SETTINGS_CUSTOM_TEMPLATES', request('custom-templates'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_SSML_EFFECT', request('set-ssml-effects'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_MAX_CHAR_LIMIT', request('set-max-chars'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_MAX_VOICE_LIMIT', request('set-max-voices'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_DEFAULT_STORAGE', request('set-storage-option'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_DEFAULT_LANGUAGE', request('language'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_DEFAULT_VOICE', request('voice'));
            $this->storeConfiguration('DAVINCI_SETTINGS_WHISPER_MAX_AUDIO_SIZE', request('set-max-audio-size'));
            $this->storeConfiguration('DAVINCI_SETTINGS_WHISPER_DEFAULT_STORAGE', request('set-whisper-storage-option'));

            Setting::where('name', 'words_filter')->update(['value' => request('words-filter')]);

            
            # Enable/Disable Main Features
            #=================================================================================
            if (request('writer-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_WRITER_FEATURE_USER', 'allow');
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_WRITER_FEATURE_USER', 'deny');
            }

            if (request('rewriter-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_REWRITER_FEATURE_USER', 'allow');
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_REWRITER_FEATURE_USER', 'deny');
            }

            if (request('smart-editor-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_SMART_EDITOR_FEATURE_USER', 'allow');
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_SMART_EDITOR_FEATURE_USER', 'deny');
            }

            if (request('chat-image-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_IMAGE_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_IMAGE_FEATURE_USER', 'deny');
            }

            if (request('chat-web-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_WEB_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_WEB_FEATURE_USER', 'deny');
            }

            if (request('chat-file-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_FILE_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_FILE_FEATURE_USER', 'deny');
            }

            if (request('code-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_CODE_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_CODE_FEATURE_USER', 'deny');
            }

            if (request('vision-for-chat-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_VISION_FOR_CHAT_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_VISION_FOR_CHAT_FEATURE_USER', 'deny');
            }

            if (request('vision-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_VISION_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_VISION_FEATURE_USER', 'deny');
            }

            if (request('wizard-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_WIZARD_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_WIZARD_FEATURE_USER', 'deny');
            }

            if (request('team-members-feature') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_TEAM_MEMBERS_FEATURE', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_TEAM_MEMBERS_FEATURE', 'deny');
            }

            if (request('chat-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_FEATURE_USER', 'deny');
            }

            if (request('image-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_IMAGE_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_IMAGE_FEATURE_USER', 'deny');
            }

            if (request('voiceover-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_FEATURE_USER', 'deny');
            }

            if (request('whisper-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_WHISPER_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_WHISPER_FEATURE_USER', 'deny');
            }

            $this->storeCheckbox(request('youtube-feature'), 'youtube_feature');
            $this->storeCheckbox(request('rss-feature'), 'rss_feature');

            $data['status'] = 200;                 
            return $data;      
        }          
    }


    public function storeAPI(Request $request)
    {
        if ($request->ajax()) {

            $this->storeConfiguration('OPENAI_SECRET_KEY', request('secret-key'));
            $this->storeConfiguration('ANTHROPIC_API_KEY', request('anthropic-api-key'));
            $this->storeConfiguration('GEMINI_API_KEY', request('gemini-api-key'));
            $this->storeConfiguration('STABLE_DIFFUSION_API_KEY', request('stable-diffusion-key'));
            $this->storeConfiguration('DAVINCI_SETTINGS_SD_KEY_USAGE', request('sd-key-usage'));
            $this->storeConfiguration('DAVINCI_SETTINGS_OPENAI_KEY_USAGE', request('openai-key-usage'));
            $this->storeConfiguration('DAVINCI_SETTINGS_PERSONAL_OPENAI_API_KEY', request('personal-openai-api'));
            $this->storeConfiguration('DAVINCI_SETTINGS_PERSONAL_SD_API_KEY', request('personal-sd-api'));
        
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_ENABLE_AZURE', request('enable-azure'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_ENABLE_GCP', request('enable-gcp'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_ENABLE_ELEVENLABS', request('enable-elevenlabs'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_ENABLE_OPENAI_STANDARD', request('enable-openai-std'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_ENABLE_OPENAI_NEURAL', request('enable-openai-nrl'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_ENABLE_AWS_STANDARD', request('enable-aws-std'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_ENABLE_AWS_NEURAL', request('enable-aws-nrl'));

            $this->storeConfiguration('AZURE_SUBSCRIPTION_KEY', request('set-azure-key'));
            $this->storeConfiguration('AZURE_DEFAULT_REGION', request('set-azure-region'));
        
            $this->storeConfiguration('ELEVENLABS_API_KEY', request('set-elevenlabs-api'));
            $this->storeConfiguration('SERPER_API_KEY', request('set-serper-api'));
            $this->storeConfiguration('PLAGIARISM_API_TOKEN', request('set-plagiarism-api'));

            $this->storeConfiguration('GOOGLE_APPLICATION_CREDENTIALS', request('gcp-configuration-path'));
            $this->storeConfiguration('GOOGLE_STORAGE_BUCKET', request('gcp-bucket'));

            $this->storeConfiguration('AWS_ACCESS_KEY_ID', request('set-aws-access-key'));
            $this->storeConfiguration('AWS_SECRET_ACCESS_KEY', request('set-aws-secret-access-key'));
            $this->storeConfiguration('AWS_DEFAULT_REGION', request('set-aws-region'));
            $this->storeConfiguration('AWS_BUCKET', request('set-aws-bucket'));

            $this->storeConfiguration('STORJ_ACCESS_KEY_ID', request('set-storj-access-key'));
            $this->storeConfiguration('STORJ_SECRET_ACCESS_KEY', request('set-storj-secret-access-key'));
            $this->storeConfiguration('STORJ_BUCKET', request('set-storj-bucket')); 

            $this->storeConfiguration('DROPBOX_APP_KEY', request('set-dropbox-app-key'));
            $this->storeConfiguration('DROPBOX_APP_SECRET', request('set-dropbox-secret-key'));
            $this->storeConfiguration('DROPBOX_ACCESS_TOKEN', request('set-dropbox-access-token'));

            $this->storeConfiguration('WASABI_ACCESS_KEY_ID', request('set-wasabi-access-key'));
            $this->storeConfiguration('WASABI_SECRET_ACCESS_KEY', request('set-wasabi-secret-access-key'));
            $this->storeConfiguration('WASABI_DEFAULT_REGION', request('set-wasabi-region'));
            $this->storeConfiguration('WASABI_BUCKET', request('set-wasabi-bucket'));

            $this->storeConfiguration('CLOUDFLARE_R2_ACCESS_KEY_ID', request('set-r2-access-key'));
            $this->storeConfiguration('CLOUDFLARE_R2_SECRET_ACCESS_KEY', request('set-r2-secret-access-key'));
            $this->storeConfiguration('CLOUDFLARE_R2_BUCKET', request('set-r2-bucket'));
            $this->storeConfiguration('CLOUDFLARE_R2_ENDPOINT', request('set-r2-endpoint'));
            $this->storeConfiguration('CLOUDFLARE_R2_URL', request('set-r2-url'));

            $this->storeValues(request('youtube-api'), 'youtube_api');


            # Enable/Disable GCP Voices
            #==================================================================================
            if (request('enable-gcp') == 'on') {
                $gcp_nrl = Vendor::where('vendor_id', 'gcp_nrl')->first();
                $gcp_nrl->enabled = 1;
                $gcp_nrl->save();

            } else {
                $gcp_nrl = Vendor::where('vendor_id', 'gcp_nrl')->first();
                $gcp_nrl->enabled = 0;
                $gcp_nrl->save();
            }


            if (request('enable-gcp') == 'on') {
                DB::table('voices')->where('vendor_id', 'gcp_nrl')->update(array('status' => 'active'));
        
            } else {
                DB::table('voices')->where('vendor_id', 'gcp_nrl')->update(array('status' => 'deactive'));
            }


            # Enable/Disable Azure Voices
            if (request('enable-azure') == 'on') {
                $azure_nrl = Vendor::where('vendor_id', 'azure_nrl')->first();
                $azure_nrl->enabled = 1;
                $azure_nrl->save();

            } else {
                $azure_nrl = Vendor::where('vendor_id', 'azure_nrl')->first();
                $azure_nrl->enabled = 0;
                $azure_nrl->save();
            }


            if (request('enable-azure') == 'on') {
                DB::table('voices')->where('vendor_id', 'azure_nrl')->update(array('status' => 'active'));
        
            } else {
                DB::table('voices')->where('vendor_id', 'azure_nrl')->update(array('status' => 'deactive'));
            }


            # Enable/Disable Openai Voices
            if (request('enable-openai-nrl') == 'on') {
                $gcp_nrl = Vendor::where('vendor_id', 'openai_nrl')->first();
                $gcp_nrl->enabled = 1;
                $gcp_nrl->save();
            } else {
                $gcp_nrl = Vendor::where('vendor_id', 'openai_nrl')->first();
                $gcp_nrl->enabled = 0;
                $gcp_nrl->save();
            }

            if (request('enable-openai-std') == 'on') {
                $gcp_std = Vendor::where('vendor_id', 'openai_std')->first();
                $gcp_std->enabled = 1;
                $gcp_std->save();

            } else {
                $gcp_std = Vendor::where('vendor_id', 'openai_std')->first();
                $gcp_std->enabled = 0;
                $gcp_std->save();
            }


            if (request('enable-openai-std') == 'on') {
                DB::table('voices')->where('vendor_id', 'openai_std')->update(array('status' => 'active'));    
            } else {
                DB::table('voices')->where('vendor_id', 'openai_std')->update(array('status' => 'deactive'));
            }

            if (request('enable-openai-nrl') == 'on') {
                DB::table('voices')->where('vendor_id', 'openai_nrl')->update(array('status' => 'active'));    
            } else {
                DB::table('voices')->where('vendor_id', 'openai_nrl')->update(array('status' => 'deactive'));
            }


             # Enable/Disable AWS Voices
             if (request('enable-aws-nrl') == 'on') {
                $aws_nrl = Vendor::where('vendor_id', 'aws_nrl')->first();
                $aws_nrl->enabled = 1;
                $aws_nrl->save();
            } else {
                $aws_nrl = Vendor::where('vendor_id', 'aws_nrl')->first();
                $aws_nrl->enabled = 0;
                $aws_nrl->save();
            }

            if (request('enable-aws-std') == 'on') {
                $aws_std = Vendor::where('vendor_id', 'aws_std')->first();
                $aws_std->enabled = 1;
                $aws_std->save();

            } else {
                $aws_std = Vendor::where('vendor_id', 'aws_std')->first();
                $aws_std->enabled = 0;
                $aws_std->save();
            }


            if (request('enable-aws-std') == 'on') {
                DB::table('voices')->where('vendor_id', 'aws_std')->update(array('status' => 'active'));    
            } else {
                DB::table('voices')->where('vendor_id', 'aws_std')->update(array('status' => 'deactive'));
            }

            if (request('enable-aws-nrl') == 'on') {
                DB::table('voices')->where('vendor_id', 'aws_nrl')->update(array('status' => 'active'));    
            } else {
                DB::table('voices')->where('vendor_id', 'aws_nrl')->update(array('status' => 'deactive'));
            }


            # Enable/Disable Elevenlabs Voices
            if (request('enable-elevenlabs') == 'on') {
                $elevenlabs_nrl = Vendor::where('vendor_id', 'elevenlabs_nrl')->first();
                $elevenlabs_nrl->enabled = 1;
                $elevenlabs_nrl->save();

            } else {
                $elevenlabs_nrl = Vendor::where('vendor_id', 'elevenlabs_nrl')->first();
                $elevenlabs_nrl->enabled = 0;
                $elevenlabs_nrl->save();
            }


            if (request('enable-elevenlabs') == 'on') {
                DB::table('voices')->where('vendor_id', 'elevenlabs_nrl')->update(array('status' => 'active'));
        
            } else {
                DB::table('voices')->where('vendor_id', 'elevenlabs_nrl')->update(array('status' => 'deactive'));
            }

            $data['status'] = 200;                 
            return $data;      
        }       
    }


    public function storeExtended(Request $request)
    {   
        if ($request->ajax()) {

            $this->storeConfiguration('DAVINCI_SETTINGS_COST_PER_IMAGE_TO_VIDEO', request('text-to-video-cost'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_MAX_MERGE_FILES', request('max-merge-files'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_MAX_BACKGROUND_AUDIO_SIZE', request('max-background-audio-size'));
            $this->storeConfiguration('DAVINCI_SETTINGS_WINDOWS_FFMPEG_PATH', request('windows-ffmpeg-path'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICE_CLONE_LIMIT_FREE_TIER_USER', request('voice-clone-limit'));

            
            # Enable/Disable Main Features
            #=================================================================================
            if (request('video-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_VIDEO_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_VIDEO_FEATURE_USER', 'deny');
            }

            if (request('video-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_VIDEO_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_VIDEO_FREE_TIER_USER', 'deny');
            }

            if (request('voice-clone-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_VOICE_CLONE_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_VOICE_CLONE_FEATURE_USER', 'deny');
            }

            if (request('voice-clone-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_VOICE_CLONE_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_VOICE_CLONE_FREE_TIER_USER', 'deny');
            }

            if (request('sound-studio-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_SOUND_STUDIO_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_SOUND_STUDIO_FEATURE_USER', 'deny');
            }

            if (request('sound-studio-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_SOUND_STUDIO_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_SOUND_STUDIO_FREE_TIER_USER', 'deny');
            }

            if (request('plagiarism-checker-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_PLAGIARISM_CHECKER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_PLAGIARISM_CHECKER_USER', 'deny');
            }

            if (request('plagiarism-checker-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_PLAGIARISM_CHECKER_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_PLAGIARISM_CHECKER_FREE_TIER_USER', 'deny');
            }

            if (request('ai-detector-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_AI_DETECTOR_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_AI_DETECTOR_FEATURE_USER', 'deny');
            }

            if (request('ai-detector-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_AI_DETECTOR_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_AI_DETECTOR_FREE_TIER_USER', 'deny');
            }

            if (request('integration-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_INTEGRATION_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_INTEGRATION_FREE_TIER_USER', 'deny');
            }

            if (request('integration-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_INTEGRATION_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_INTEGRATION_FEATURE_USER', 'deny');
            }

            if (request('photo-studio-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_PHOTO_STUDIO_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_PHOTO_STUDIO_FREE_TIER_USER', 'deny');
            }

            if (request('photo-studio-feature-user') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_PHOTO_STUDIO_FEATURE_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_PHOTO_STUDIO_FEATURE_USER', 'deny');
            }

            $data['status'] = 200;                 
            return $data;      
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeTrial(Request $request)
    {
        if ($request->ajax()) {  

            $this->storeConfiguration('DAVINCI_SETTINGS_TEMPLATES_ACCESS_USER', request('templates-user'));
            $this->storeConfiguration('DAVINCI_SETTINGS_CHATS_ACCESS_USER', request('chat-user'));
            $this->storeConfiguration('DAVINCI_SETTINGS_DEFAULT_MODEL_USER', request('default-model-user-bot'));
            $this->storeConfiguration('DAVINCI_SETTINGS_DEFAULT_MODEL_USER_TEMPLATE', request('default-model-user-template'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FREE_TIER_GPT_3_TURBO_CREDITS', request('gpt-3-turbo'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FREE_TIER_GPT_4_TURBO_CREDITS', request('gpt-4-turbo'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FREE_TIER_GPT_4_CREDITS', request('gpt-4'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FREE_TIER_GPT_4o_CREDITS', request('gpt-4o'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FREE_TIER_FINE_TUNE_CREDITS', request('fine-tune'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FREE_TIER_CLAUDE_3_OPUS_CREDITS', request('claude-3-opus'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FREE_TIER_CLAUDE_3_SONNET_CREDITS', request('claude-3-sonnet'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FREE_TIER_CLAUDE_3_HAIKU_CREDITS', request('claude-3-haiku'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FREE_TIER_GEMINI_PRO_CREDITS', request('gemini-pro'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FREE_TIER_DALLE_IMAGES', request('free-tier-dalle-images'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FREE_TIER_SD_IMAGES', request('free-tier-sd-images'));
            $this->storeConfiguration('DAVINCI_SETTINGS_MAX_RESULTS_LIMIT_USER', request('max-results-user'));
            $this->storeConfiguration('DAVINCI_SETTINGS_TEAM_MEMBERS_QUANTITY', request('team-members-quantity'));
            $this->storeConfiguration('DAVINCI_SETTINGS_FILE_RESULT_DURATION_USER', request('file-result-duration'));
            $this->storeConfiguration('DAVINCI_SETTINGS_DOCUMENT_RESULT_DURATION_USER', request('document-result-duration'));
            $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_CSV_FILE_SIZE_USER', request('max-csv-size'));
            $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_PDF_FILE_SIZE_USER', request('max-pdf-size'));
            $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_WORD_FILE_SIZE_USER', request('max-word-size'));
            $this->storeConfiguration('DAVINCI_SETTINGS_VOICEOVER_FREE_TIER_WELCOME_CHARS', request('set-free-chars'));
            $this->storeConfiguration('DAVINCI_SETTINGS_WHISPER_FREE_TIER_WELCOME_MINUTES', request('set-free-minutes'));

            $this->storeValues(request('gpt-4o-mini'), 'gpt_4o_mini_credits');
            $this->storeValues(request('o1-preview'), 'o1_preview_credits');
            $this->storeValues(request('o1-mini'), 'o1_mini_credits');

            $voiceover_vendors = '';
            if (!is_null(request('voiceover_vendors'))) {
                foreach (request('voiceover_vendors') as $key => $value) {
                    if ($key === array_key_last(request('voiceover_vendors'))) {
                        $voiceover_vendors .= $value; 
                    } else {
                        $voiceover_vendors .= $value . ', '; 
                    }
                    
                }
                $vendors = "'". $voiceover_vendors . "'";
                $this->storeWithQuotes('DAVINCI_SETTINGS_VOICEOVER_FREE_TIER_VENDORS', $vendors);
            }


            $selected_models = '';
            if (!is_null(request('models_list'))) {
                foreach (request('models_list') as $key => $value) {
                    if ($key === array_key_last(request('models_list'))) {
                        $selected_models .= $value; 
                    } else {
                        $selected_models .= $value . ', '; 
                    }
                    
                }
                $models = "'". $selected_models . "'";
                $this->storeWithQuotes('DAVINCI_SETTINGS_FREE_TIER_MODELS', $models);
            }

            
            # Enable/Disable Main Features
            #=================================================================================
            if (request('writer-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_WRITER_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_WRITER_FREE_TIER_USER', 'deny');
            }

            if (request('vision-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_VISION_ACCESS_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_VISION_ACCESS_FREE_TIER_USER', 'deny');
            }

            if (request('wizard-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_WIZARD_ACCESS_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_WIZARD_ACCESS_FREE_TIER_USER', 'deny');
            }

            if (request('chat-file-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_FILE_ACCESS_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_FILE_ACCESS_FREE_TIER_USER', 'deny');
            }

            if (request('chat-image-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_IMAGE_ACCESS_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_IMAGE_ACCESS_FREE_TIER_USER', 'deny');
            }

            if (request('chat-web-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_WEB_ACCESS_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_CHAT_WEB_ACCESS_FREE_TIER_USER', 'deny');
            }

            if (request('smart-editor-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_SMART_EDITOR_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_SMART_EDITOR_FREE_TIER_USER', 'deny');
            }

            if (request('rewriter-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_REWRITER_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_REWRITER_FREE_TIER_USER', 'deny');
            }

            if (request('internet-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_INTERNET_ACCESS_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_INTERNET_ACCESS_FREE_TIER_USER', 'deny');
            }

            if (request('brand-voice-user-access') == 'on') {
                $this->storeConfiguration('DAVINCI_SETTINGS_BRAND_VOICE_FREE_TIER_USER', 'allow'); 
            } else {
                $this->storeConfiguration('DAVINCI_SETTINGS_BRAND_VOICE_FREE_TIER_USER', 'deny');
            }

            $this->storeCheckbox(request('youtube-user-access'), 'youtube_feature_free_tier');
            $this->storeCheckbox(request('rss-user-access'), 'rss_feature_free_tier');

            $data['status'] = 200;                 
            return $data;      
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


    private function storeWithQuotes($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {

            file_put_contents($path, str_replace(
                $key . '=' . '\'' . env($key) . '\'', $key . '=' . $value, file_get_contents($path)
            ));

        }
    }


    private function storeCheckbox($checkbox, $field_name)
    {
        if ($checkbox == 'on') {
            $status = true; 
        } else {
            $status = false;
        }

        $settings = MainSetting::first();
        $settings->update([
            $field_name => $status
        ]);
    }


    private function storeValues($value, $field_name)
    {
        $settings = MainSetting::first();
        $settings->update([
            $field_name => $value
        ]);
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showKeys(Request $request)
    {
        if ($request->ajax()) {
            $data = ApiKey::orderBy('engine', 'asc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function($row){
                    $actionBtn = '<div>      
                                    <a class="editButton" id="' . $row["id"] . '" href="#"><i class="fa fa-edit table-action-buttons view-action-button" title="Update API Key"></i></a>          
                                    <a class="activateButton" id="' . $row["id"] . '" href="#"><i class="fa fa-check table-action-buttons request-action-button" title="Activate or Deactivate API Key"></i></a>
                                    <a class="deleteButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="Delete API Key"></i></a> 
                                </div>';     
                    return $actionBtn;
                })
                ->addColumn('created-on', function($row){
                    $created_on = '<span class="font-weight-bold">'.date_format($row["created_at"], 'd M Y').'</span><br><span>'.date_format($row["created_at"], 'H:i A').'</span>';
                    return $created_on;
                })
                ->addColumn('engine-name', function($row){
                    $name = ($row['engine'] == 'openai') ? 'OpenAI' : 'Stable Diffusion';
                    $user = '<span class="font-weight-bold">'. ucfirst($name) .'</span>';
                    return $user;
                }) 
                ->addColumn('status', function($row){
                    $status = ($row['status']) ? 'active' : 'deactive';
                    $user = '<span class="cell-box status-'.$status.'">'. ucfirst($status) .'</span>';
                    return $user;
                })
                ->rawColumns(['actions', 'created-on', 'engine-name', 'status'])
                ->make(true);
                    
        }

        return view('admin.davinci.configuration.keys');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function createKeys(Request $request)
    {
        return view('admin.davinci.configuration.create');
    }


     /**
     * Store review post properly in database
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeKeys(Request $request)
    {
        request()->validate([
            'engine' => 'required',
            'api_key' => 'required',
            'status' => 'required',
        ]);  

        ApiKey::create([
            'engine' => $request->engine,
            'api_key' => $request->api_key,
            'status' => $request->status,
        ]);

        toastr()->success(__('API Key successfully stored'));
        return redirect()->route('admin.davinci.configs.keys');
    }


    /**
     * Update the api key
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {   
        if ($request->ajax()) {

            $template = ApiKey::where('id', request('id'))->firstOrFail();
            
            $template->update(['api_key' => request('name')]);
            return  response()->json('success');
        } 
    }


    /**
     * Activate the api key
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function activate(Request $request)
    {   
        if ($request->ajax()) {

            $template = ApiKey::where('id', request('id'))->firstOrFail();
            
            if ($template->status) {
                $template->update(['status' => false]);
                return  response()->json('deactive');
            } else {
                $template->update(['status' => true]);
                return  response()->json('active');
            }   
        } 
    }


    /**
     * Delete the api key
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {   
        if ($request->ajax()) {

            $name = ApiKey::find(request('id'));

            if($name) {

                $name->delete();

                return response()->json('success');

            } else{
                return response()->json('error');
            } 
        } 
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showPhotoStudio(Request $request)
    {
        $studio = SdCost::first();
        return view('admin.davinci.configuration.sd', compact('studio'));
    }


    /**
     * Store photo studio costs in database
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storePhotoStudio(Request $request)
    {
        $studio = SdCost::first();
        $studio->update([
            'sd_photo_studio_reimagine' => request('reimagine'),
            'sd_photo_studio_inpaint' => request('inpaint'), 
            'sd_photo_studio_search_replace' => request('search'), 
            'sd_photo_studio_outpaint' => request('outpaint'),
            'sd_photo_studio_erase_object' => request('erase'), 
            'sd_photo_studio_remove_background' => request('background'), 
            'sd_photo_studio_structure' => request('structure'),
            'sd_photo_studio_sketch' => request('sketch'), 
            'sd_photo_studio_creative_upscaler' => request('creative'), 
            'sd_photo_studio_conservative_upscaler' => request('conservative'), 
            'sd_photo_studio_text' => request('text'), 
        ]);

        toastr()->success(__('Photo Studio feature costs updated'));
        return redirect()->route('admin.davinci.configs.sd');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showCosts(Request $request)
    {
        $prices = VendorPrice::first();
        return view('admin.davinci.configuration.costs', compact('prices'));
    }


    /**
     * Store photo studio costs in database
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeCosts(Request $request)
    {
        $prices = VendorPrice::first();
        $prices->update([
            'gpt_3t' => request('gpt_3t'),
            'gpt_4t' => request('gpt_4t'),
            'gpt_4' => request('gpt_4'),
            'gpt_4o' => request('gpt_4o'),
            'gpt_4o_mini' => request('gpt_4o_mini'),
            'fine_tuned' => request('fine_tuned'),
            'whisper' => request('whisper'),
            'dalle_2' => request('dalle_2'),
            'dalle_3' => request('dalle_3'),
            'dalle_3_hd' => request('dalle_3_hd'),
            'claude_3_opus' => request('opus'),
            'claude_3_sonnet' => request('sonnet'),
            'claude_3_haiku' => request('haiku'),
            'gemini_pro' => request('gemini'),
            'sd' => request('sd'),
            'aws_tts' => request('aws'),
            'azure_tts' => request('azure'),
            'gcp_tts' => request('gcp'),
            'elevenlabs_tts' => request('elevenlabs'),
            'openai_tts' => request('openai'),
        ]);

        toastr()->success(__('Vendor service costs updated successfully'));
        return redirect()->route('admin.davinci.configs.costs');
    }


     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showFineTune(Request $request)
    {
        if ($request->ajax()) {
            $data = FineTune::orderBy('created_at', 'desc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function($row){
                    $actionBtn = '<div>              
                                    <a class="deleteButton" id="'. $row["task_id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="Delete Fine Tune Model"></i></a> 
                                </div>';     
                    return $actionBtn;
                })
                ->rawColumns(['actions'])
                ->make(true);
                    
        }

        $this->checkModels();

        $models = FineTuneModel::all();

        return view('admin.davinci.configuration.fine-tune.index', compact('models'));
    }


    public function createFineTune(Request $request)
    {
        if($request->hasFile('file')){ 

            $file_extension = $request->file('file')->getClientOriginalExtension();
            $path = $request->file('file')->getRealPath();

            if ($file_extension != 'jsonl') {
                toastr()->error(__('Only jsonl file are allowed to be uploaded for training'));
                return redirect()->back();
            }

            try {
                $upload = OpenAI::files()->upload([
                    'purpose' => 'fine-tune',
                    'file' => fopen($path, 'r'),
                ]);

                $result = OpenAI::fineTuning()->createJob([
                    "model" => $request->model,
                    "training_file" => $upload->id,
                    'validation_file' => null,
                ]);

                FineTune::create([
                    'task_id' => $result->id,
                    'base_model' => $request->model,
                    'bytes' => $upload->bytes,
                    'model_name' => ucfirst($request->name),
                    'file_name' => $upload->filename,
                    'file_id' => $upload->id,
                    'description' => $request->description,
                    'status' => 'processing',
                ]);

                toastr()->success(__('Fine Tune task has been successfully created'));
                return redirect()->back();
           
            } catch(Exception $e) {
                \Log::info($e->getMessage());
                toastr()->error($e->getMessage());
                return redirect()->back();
            }

        } else {
            toastr()->error(__('JSONL training file is required'));
            return redirect()->back();
        }

    }


    public function checkModels() 
    {
        $jobs = FineTune::where('status', 'processing')->get();

        foreach ($jobs as $job) {

            try {

                $response = OpenAI::fineTuning()->retrieveJob($job->task_id);

                if ($response->status == 'succeeded') {
                    $job->update([
                        'status' => 'succeeded',
                        'result_model' => $response->fineTunedModel
                    ]);
    
                    FineTuneModel::create([
                        'model' => $response->fineTunedModel,
                        'name' => $job->model_name,
                        'description' => $job->description,
                    ]);
                }
            } catch(Exception $e) {
                \Log::info($e->getMessage());
                toastr()->error($e->getMessage());
                return redirect()->back();
            }
           
        }
        
    }


    public function deleteFineTune(Request $request)
    {
        $model = FineTune::where('task_id', $request->id)->first();
        OpenAI::files()->delete($model->file_id);
        FineTuneModel::where('model', $model->result_model)->delete();
        $model->delete();

        return response()->json('success');
    }


}


