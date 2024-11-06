<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use App\Services\Statistics\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Orhanerday\OpenAi\OpenAi;
use App\Models\SubscriptionPlan;
use App\Models\Code;
use App\Models\User;
use App\Models\ApiKey;
use App\Models\FineTuneModel;
use App\Services\HelperService;


class CodeController extends Controller
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
        # Apply proper model based on role and subsciption
        if (auth()->user()->group == 'user') {
            $models = explode(',', config('settings.free_tier_models'));
        } elseif (!is_null(auth()->user()->plan_id)) {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            $models = explode(',', $plan->model);
        } else {            
            $models = explode(',', config('settings.free_tier_models'));
        }

        $fine_tunes = FineTuneModel::all();
        $default_model = auth()->user()->default_model_template;

        return view('user.codex.index', compact('models', 'fine_tunes', 'default_model'));
    }


    /**
	*
	* Process Davinci Code
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function process(Request $request) 
    {
        if ($request->ajax()) {

            if (config('settings.personal_openai_api') == 'allow') {
                if (is_null(auth()->user()->personal_openai_key)) {
                    $data['status'] = 'error';
                    $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                    return $data; 
                } else {
                    $open_ai = new OpenAi(auth()->user()->personal_openai_key);
                } 
    
            } elseif (!is_null(auth()->user()->plan_id)) {
                $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                if ($check_api->personal_openai_api) {
                    if (is_null(auth()->user()->personal_openai_key)) {
                        $data['status'] = 'error';
                        $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                        return $data; 
                    } else {
                        $open_ai = new OpenAi(auth()->user()->personal_openai_key);
                    }
                } else {
                    if (config('settings.openai_key_usage') !== 'main') {
                       $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                       array_push($api_keys, config('services.openai.key'));
                       $key = array_rand($api_keys, 1);
                       $open_ai = new OpenAi($api_keys[$key]);
                   } else {
                       $open_ai = new OpenAi(config('services.openai.key'));
                   }
               }
    
            } else {
                if (config('settings.openai_key_usage') !== 'main') {
                    $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                    array_push($api_keys, config('services.openai.key'));
                    $key = array_rand($api_keys, 1);
                    $open_ai = new OpenAi($api_keys[$key]);
                } else {
                    $open_ai = new OpenAi(config('services.openai.key'));
                }
            }

            $verify = $this->api->verify_license();
            if($verify['dota']!=622220){return;} 

            # Check if user has access to the template
            if (auth()->user()->group == 'user') {
                if (config('settings.code_feature_user') != 'allow') {
                    $data['status'] = 'error';
                    $data['message'] = __('AI Code feature is not available for your account, subscribe to get access');
                    return $data;
                } 

            } elseif (!is_null(auth()->user()->group)) {
                $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                if ($plan) {
                    if (!$plan->code_feature) {
                        $data['status'] = 'error';
                        $data['message'] = __('AI Code feature is not available for your subscription plan');
                        return $data;
    
                    }
                }
            }   
            
            # Verify if user has enough credits
            $verify = HelperService::creditCheck($request->model, 50);
            if (isset($verify['status'])) {
                if ($verify['status'] == 'error') {
                    return $verify;
                }
            }

            if ($request->language != 'html' || $request->language == 'none') {
                $prompt = "You are a helpful assistant that writes code. Write a good code in " . $request->language . ' programming language';
            } elseif ($request->language == 'html') {
                $prompt = "You are a helpful assistant that writes html code.";
            } else {
                $prompt = "You are a helpful assistant that writes code.";
            }
           

            $complete = $open_ai->chat([
                'model' => $request->model,
                'messages' => [
                    [
                        "role" => "system",
                        "content" => $prompt,
                    ],
                    [
                        "role" => "user",
                        "content" => $request->instructions,
                    ],
                ],
                'temperature' => 1,
                'max_tokens' => 3500,
            ]);

            $response = json_decode($complete , true);
            $uploading = new UserService();
            $upload = $uploading->upload();
            if($upload['dota']!=622220){return;}   

            if (isset($response['choices'])) {

                $text = $response['choices'][0]['message']['content'];
                $tokens = $response['usage']['total_tokens'];

                # Update credit balance
                HelperService::updateBalance($tokens, $request->model);
                
                $code = new Code();
                $code->user_id = auth()->user()->id;
                $code->model = $request->language;
                $code->instructions = $request->instructions;
                $code->save();

                $data['text'] = $text;
                $data['status'] = 'success';
                $data['id'] = $code->id;
                $data['old'] = auth()->user()->available_words + auth()->user()->available_words_prepaid;
                $data['current'] = auth()->user()->available_words + auth()->user()->available_words_prepaid - $tokens;
                return $data; 

            } else {

                if (isset($response['error']['message'])) {
                    $message = $response['error']['message'];
                } else {
                    $message = __('There is an issue with your openai account');
                }

                $data['status'] = 'error';
                $data['message'] = $message;
                return $data;
            }
           
        }
	}


    /**
	*
	* Save changes
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function save(Request $request) 
    {
        if ($request->ajax()) {

            $verify = $this->api->verify_license();
            if($verify['status']!=true){return false;}

            $document = Code::where('id', request('id'))->first(); 

            if ($document->user_id == Auth::user()->id){

                $document->code = $request->text;
                $document->title = $request->title;
                $document->save();

                $data['status'] = 'success';
                return $data;  
    
            } else{

                $data['status'] = 'error';
                return $data;
            }  
        }
	}


}
