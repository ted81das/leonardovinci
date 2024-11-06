<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use App\Services\Statistics\UserService;
use Illuminate\Support\Facades\Auth;
use App\Traits\VoiceToneTrait;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\FavoriteTemplate;
use App\Models\CustomTemplate;
use App\Models\SubscriptionPlan;
use App\Models\Template;
use App\Models\Content;
use App\Models\Workbook;
use App\Models\Language;
use App\Models\ApiKey;
use App\Models\User;
use App\Models\BrandVoice;
use App\Models\FineTuneModel;
use App\Services\HelperService;


class RewriterController extends Controller
{
    use VoiceToneTrait;

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
    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $languages = Language::orderBy('languages.language', 'asc')->get();
        $workbooks = Workbook::where('user_id', auth()->user()->id)->latest()->get();

        $brands = BrandVoice::where('user_id', auth()->user()->id)->get();

        if (!is_null(auth()->user()->plan_id)) {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            $brand_feature = $plan->brand_voice_feature;
        } else {
            if (config('settings.brand_voice_user_access') == 'allow') {
                $brand_feature = true;
            } else {
                $brand_feature = false;
            }
        }

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

        if (auth()->user()->group == 'user') {
            if (config('settings.rewriter_user_access') != 'allow') {
                toastr()->warning(__('AI ReWriter feature is not available for free tier users, subscribe to get a proper access'));
                return redirect()->route('user.plans');
            } else {
                return view('user.rewriter.index', compact('languages', 'workbooks', 'brands', 'brand_feature', 'models', 'fine_tunes', 'default_model'));
            }
        } elseif (auth()->user()->group == 'subscriber') {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($plan->rewriter_feature == false) {     
                toastr()->warning(__('Your current subscription plan does not include support for AI ReWriter feature'));
                return redirect()->back();                   
            } else {
                return view('user.rewriter.index', compact('languages', 'workbooks', 'brands', 'brand_feature', 'models', 'fine_tunes', 'default_model'));
            }
        } else {
            return view('user.rewriter.index', compact('languages', 'workbooks', 'brands', 'brand_feature', 'models', 'fine_tunes', 'default_model'));
        }
    }


     /**
	*
	* Process Davinci
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function generate(Request $request) 
    {

        if ($request->ajax()) {
            $prompt = '';
            $max_tokens = '';
            $counter = 1;
            $input_title = '';
            $input_keywords = '';
            $input_description = '';

            # Check personal API keys
            if (config('settings.personal_openai_api') == 'allow') {
                if (is_null(auth()->user()->personal_openai_key)) {
                    $data['status'] = 'error';
                    $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                    return $data;
                }     
            } elseif (!is_null(auth()->user()->plan_id)) {
                $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                if ($check_api->personal_openai_api) {
                    if (is_null(auth()->user()->personal_openai_key)) {
                        $data['status'] = 'error';
                        $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                        return $data;
                    } 
                }    
            } 

            # Verify if user has enough credits
            $verify = HelperService::creditCheck($request->model, 100);
            if (isset($verify['status'])) {
                if ($verify['status'] == 'error') {
                    return $verify;
                }
            }

            
            $flag = Language::where('language_code', $request->language)->first();
            $uploading = new UserService();
            $upload = $uploading->prompt();
            if($upload['dota']!=622220){return;}


            if ($request->language == 'en-US') {
                $prompt = 'Rewrite the following target text below.';
            } else {
                $prompt = "Provide response in " . $flag->language . '.\n\n Rewrite the target text below. \n\n';
            }

            if (isset($request->tone)) {
                $prompt = $prompt . ' \n\n Voice of tone of the response text must be ' . $request->tone . '.';
            }     
            
            if (isset($request->view_point)) {
                if ($request->view_point != 'none')
                    $prompt = $prompt . ' \n\n Rewrite the article in the view point of ' . $request->view_point . ' person. \n\n';
            }

            $prompt = $prompt . 'Target text: ' . $request->prompt;

            # Add Brand information
            if ($request->brand == 'on') {
                $brand = BrandVoice::where('id', $request->company)->first();

                if ($brand) {
                    $product = '';
                    if ($request->service != 'none') {
                        foreach($brand->products as $key => $value) {
                            if ($key == $request->service)
                                $product = $value;
                        }
                    } 
                    
                    if ($request->service != 'none') {
                        $prompt .= ".\n Focus on my company and {$product['type']}'s information: \n";
                    } else {
                        $prompt .= ".\n Focus on my company's information: \n";
                    }
                    
                    if ($brand->name) {
                        $prompt .= "The company's name is {$brand->name}. ";
                    }

                    if ($brand->description) {
                        $prompt .= "The company's description is {$brand->description}. ";
                    }

                    if ($brand->website) {
                        $prompt .= ". The company's website is {$brand->website}. ";
                    }

                    if ($brand->tagline) {
                        $prompt .= "The company's tagline is {$brand->tagline}. ";
                    }

                    if ($brand->audience) {
                        $prompt .= "The company's target audience is: {$brand->audience}. ";
                    }

                    if ($brand->industry) {
                        $prompt .= "The company focuses in: {$brand->industry}. ";
                    }
    
                    if ($product) {
                        if ($product['name']) {
                            $prompt .= "The {$product['type']}'s name is {$product['name']}. \n";
                        }

                        if ($product['description']) {
                            $prompt .= "The {$product['type']} is about {$product['description']}. ";
                        }                        
                    }
                }
            }


            $plan_type = (auth()->user()->plan_id) ? 'paid' : 'free';
            
            $content = new Content();
            $content->user_id = auth()->user()->id;
            $content->input_text = $prompt;
            $content->language = $request->language;
            $content->language_name = $flag->language;
            $content->language_flag = $flag->language_flag;
            $content->template_code = $request->code;
            $content->template_name = 'AI ReWrite';
            $content->icon = '<i class="fa-solid fa-pen-line rewriter-icon"></i>';
            $content->group = 'rewriter';
            $content->tokens = 0;
            $content->plan_type = $plan_type;
            $content->model = $request->model;
            $content->save();

            $data['status'] = 'success';     
            $data['temperature'] = $request->creativity;     
            $data['id'] = $content->id;
            $data['language'] = $request->language;
            return $data;            

        }
	}


     /**
	*
	* Process Davinci
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function process(Request $request) 
    {
        if (config('settings.personal_openai_api') == 'allow') {
            config(['openai.api_key' => auth()->user()->personal_openai_key]);         
        } elseif (!is_null(auth()->user()->plan_id)) {
            $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($check_api->personal_openai_api) {
                config(['openai.api_key' => auth()->user()->personal_openai_key]);                
            } else {
                if (config('settings.openai_key_usage') !== 'main') {
                    $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                    array_push($api_keys, config('services.openai.key'));
                    $key = array_rand($api_keys, 1);
                    config(['openai.api_key' => $api_keys[$key]]);
                } else {
                    config(['openai.api_key' => config('services.openai.key')]);
                }
            }
        } else {
            if (config('settings.openai_key_usage') !== 'main') {
                $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                array_push($api_keys, config('services.openai.key'));
                $key = array_rand($api_keys, 1);
                config(['openai.api_key' => $api_keys[$key]]);
            } else {
                config(['openai.api_key' => config('services.openai.key')]);
            }
        }
        
        $model = '';

        $content_id = $request->content_id;
        $temperature = $request->temperature;
        $language = $request->language;
        $content = Content::where('id', $content_id)->first();
        $prompt = $content->input_text;  
        $model = $content->model;

        return response()->stream(function () use($model, $prompt, $content_id, $temperature, $language) {

            $text = "";

            try {

                $results = OpenAI::chat()->createStreamed([
                    'model' => $model,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt]
                    ],
                    'frequency_penalty' => 0,
                    'presence_penalty' => 0,
                    'temperature' => (float)$temperature,
                ]);

                $output = "";
                $responsedText = "";
                foreach ($results as $result) {
                    
                    if (isset($result['choices'][0]['delta']['content'])) {
                        $raw = $result['choices'][0]['delta']['content'];
                        $clean = str_replace(["\r\n", "\r", "\n"], "<br/>", $raw);
                        $text .= $raw;
    
                        echo 'data: ' . $clean ."\n\n";
                        ob_flush();
                        flush();
                        usleep(400);
                    }
    
    
                    if (connection_aborted()) { break; }
                }


            } catch (\Exception $exception) {
                echo "data: " . $exception->getMessage();
                echo "\n\n";
                ob_flush();
                flush();
                echo 'data: [DONE]';
                echo "\n\n";
                ob_flush();
                flush();
                usleep(50000);
            }
           

            $words = count(explode(' ', ($text)));
            HelperService::updateBalance($words, $model); 
            # Update credit balance
            // if ($language != 'cmn-CN' && $language != 'ja-JP') {
            //     $words = count(explode(' ', ($text)));
            //     $this->updateBalance($words); 
            // } else {
            //     $words = $this->updateBalanceKanji($text);
            // }
             
            $content = Content::where('id', $content_id)->first();
            $content->tokens = $words;
            $content->words = $words;
            $content->save();


            echo 'data: [DONE]';
            echo "\n\n";
            ob_flush();
            flush();
            usleep(40000);
            
            
        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',            
        ]);

	}


    public function custom(Request $request)
    {
        # Check API keys
        if (config('settings.personal_openai_api') == 'allow') {
            if (is_null(auth()->user()->personal_openai_key)) {
                return response()->json(["status" => "error", 'message' => __('You must include your personal Openai API key in your profile settings first')]);
            } else {
                config(['openai.api_key' => auth()->user()->personal_openai_key]); 
            } 
        } elseif (!is_null(auth()->user()->plan_id)) {
            $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($check_api->personal_openai_api) {
                if (is_null(auth()->user()->personal_openai_key)) {
                    return response()->json(["status" => "error", 'message' => __('You must include your personal Openai API key in your profile settings first')]);
                } else {
                    config(['openai.api_key' => auth()->user()->personal_openai_key]); 
                }
            } else {
                if (config('settings.openai_key_usage') !== 'main') {
                   $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                   array_push($api_keys, config('services.openai.key'));
                   $key = array_rand($api_keys, 1);
                   config(['openai.api_key' => $api_keys[$key]]);
               } else {
                    config(['openai.api_key' => config('services.openai.key')]);
               }
           }
        } else {
            if (config('settings.openai_key_usage') !== 'main') {
                $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                array_push($api_keys, config('services.openai.key'));
                $key = array_rand($api_keys, 1);
                config(['openai.api_key' => $api_keys[$key]]);
            } else {
                config(['openai.api_key' => config('services.openai.key')]);
            }
        }


        if (isset($request->model)) {
            $model = $request->model;
        } elseif (isset(auth()->user()->default_model_template)) {
            $model = auth()->user()->default_model_template;
        } else {
            $model = 'gpt-4o-mini';
        }
        
        # Verify if user has enough credits
        $verify = HelperService::creditCheck($model, 100);
        if (isset($verify['status'])) {
            if ($verify['status'] == 'error') {
                return $verify;
            }
        }


        if ($request->content == null || $request->content == "") {
            return response()->json(["status" => "success", "message" => ""]);
        }

        if (isset($request->language)) {
            $language = Language::where('language_code', $request->language)->first();
            $prompt = $request->prompt . '. Provide response in the following language: '. $language->language;
        } else {
            $prompt = $request->prompt . '. If the task is not related to translation, return response in the language of the content text.';
        }

        $completion = OpenAI::chat()->create([
            'model' => $model,
            'temperature' => 0.9,
            'messages' => [[
                'role' => 'user',
                'content' => "$request->prompt:\n\n$request->content"
            ]]
        ]);


        $words = count(explode(' ', ($completion->choices[0]->message->content)));
        HelperService::updateBalance($words, $model); 

        return response()->json(["status" => "success", "message" => $completion->choices[0]->message->content]);
    }


    /**
	*
	* Show brand products
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function brand(Request $request) 
    {
        if ($request->ajax()) {    

            $brand = BrandVoice::where('id', request('brand'))->first(); 

            if ($brand->user_id == Auth::user()->id){

                $data['status'] = 'success';
                $data['products'] = $brand->products;
                return $data;  
    
            } else{

                $data['status'] = 'error';
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

            $uploading = new UserService();
            $upload = $uploading->prompt();
            if($upload['data']!=633855){return;}    

            $document = Content::where('id', request('id'))->first(); 

            if ($document->user_id == Auth::user()->id){

                $document->result_text = $request->text;
                $document->title = $request->title;
                $document->workbook = $request->workbook;
                $document->save();

                $data['status'] = 'success';
                return $data;  
    
            } else{

                $data['status'] = 'error';
                return $data;
            } 
        }
	}


    /**
	*
	* Set favorite status
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function favorite(Request $request) 
    {
        if ($request->ajax()) { 

            $favorite = FavoriteTemplate::where('template_code', $request->code)->where('user_id', auth()->user()->id)->first();

            if ($favorite) {

                $favorite->delete();

                $data['status'] = 'success';
                $data['set'] = true;
                return $data;  
    
            } else{

                $new_favorite = new FavoriteTemplate();
                $new_favorite->user_id = auth()->user()->id;
                $new_favorite->template_code = $request->code;
                $new_favorite->save();

                $data['status'] = 'success';
                $data['set'] = false;
                return $data; 
            }  
        }
	}


}
