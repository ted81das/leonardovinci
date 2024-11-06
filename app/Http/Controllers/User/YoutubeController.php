<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use App\Traits\VoiceToneTrait;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\SubscriptionPlan;
use App\Models\Content;
use App\Models\Workbook;
use App\Models\Language;
use App\Models\ApiKey;
use App\Models\User;
use App\Models\MainSetting;
use App\Models\BrandVoice;
use App\Services\HelperService;
use Exception;


class YoutubeController extends Controller
{
    use VoiceToneTrait;
    private Response $response;

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

        $settings = MainSetting::first();
        if (auth()->user()->group == 'user') {
            if ($settings->youtube_feature_free_tier) {
                return view('user.youtube.index', compact('languages', 'workbooks', 'brands', 'brand_feature'));
            } else {
                toastr()->warning(__('AI Youtube feature is not available for free tier users, subscribe to get a proper access'));
                return redirect()->route('user.plans');
            }
        } elseif (auth()->user()->group == 'subscriber') {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($plan->youtube_feature == false) {     
                toastr()->warning(__('Your current subscription plan does not include support for AI Youtube feature'));
                return redirect()->back();                   
            } else {
                return view('user.youtube.index', compact('languages', 'workbooks', 'brands', 'brand_feature'));
            }
        } else {
            return view('user.youtube.index', compact('languages', 'workbooks', 'brands', 'brand_feature'));
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

            $video_id = $this->parseURL($request->url);
            $youtube = $this->youtube($video_id);
            $prompt = '';

            switch ($request->action) {
                case 'post': $prompt = 'Create full blog post about this youtube video details: ' . $youtube. '.'; break;
                case 'outline': $prompt = 'Create detailed outline for this youtube video details: ' . $youtube. '.'; break;
                case 'explain': $prompt = 'Write a detailed explanation about this youtube video details: ' . $youtube. '.'; break;
                case 'description': $prompt = 'Create a detailed meaningful descriptoin for this youtube video details: ' . $youtube. '.'; break;
                case 'summarize': $prompt = 'Create a summarize for this youtube video details: ' . $youtube. '.'; break;
                case 'compare': $prompt = 'Write a detailed pros and cons this youtube video details: ' . $youtube. '.'; break;
            }
            
            $flag = Language::where('language_code', $request->language)->first();

            $prompt .= "Provide response in " . $flag->language . '.';

            if (isset($request->tone)) {
                $prompt = $prompt . ' \n\n Voice of tone of the text must be ' . $request->tone . '.';
            }     
            
            if (isset($request->view_point)) {
                if ($request->view_point != 'none')
                    $prompt = $prompt . ' \n\n The point of view must be in ' . $request->view_point . ' person. \n\n';
            }

            $plan_type = (auth()->user()->plan_id) ? 'paid' : 'free';
            
            $content = new Content();
            $content->user_id = auth()->user()->id;
            $content->input_text = $prompt;
            $content->language = $request->language;
            $content->language_name = $flag->language;
            $content->language_flag = $flag->language_flag;
            $content->template_code = $request->code;
            $content->template_name = 'AI Youtube';
            $content->icon = '<i class="fa-brands fa-youtube ad-icon"></i>';
            $content->group = 'youtube';
            $content->tokens = 0;
            $content->plan_type = $plan_type;
            $content->save();

            $data['status'] = 'success';     
            $data['temperature'] = $request->creativity;     
            $data['id'] = $content->id;
            $data['language'] = $request->language;
            $data['model'] = $request->model;
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
        

        $content_id = $request->content_id;
        $temperature = $request->temperature;
        $language = $request->language;
        $model = $request->model;
        $content = Content::where('id', $content_id)->first();
        $prompt = $content->input_text;  

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
           

            # Update credit balance
            $words = count(explode(' ', ($text)));
            HelperService::updateBalance($words, $model); 
             

            $content = Content::where('id', $content_id)->first();
            $content->model = $model;
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


        # Verify if user has enough credits
        $model = 'gpt-3.5-turbo-0125';

        # Verify if user has enough credits
        $verify = HelperService::creditCheck($model, 100);
        if (isset($verify['status'])) {
            if ($verify['status'] == 'error') {
                return response()->json(["status" => "error", 'message' => __('Not enough word balance to proceed, subscribe or top up your word balance and try again')]);
            }
        }

        if ($request->content == null || $request->content == "") {
            return response()->json(["status" => "success", "message" => ""]);
        }

        $completion = OpenAI::chat()->create([
            'model' => "gpt-3.5-turbo",
            'temperature' => 0.9,
            'messages' => [[
                'role' => 'user',
                'content' => "$request->prompt:\n\n$request->content"
            ]]
        ]);


        $words = count(explode(' ', ($completion->choices[0]->message->content)));
        $this->updateBalance($words); 

        return response()->json(["status" => "success", "message" => $completion->choices[0]->message->content]);
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


    private function parseURL($url)
    {
        $video_id = explode("?v=", $url); 
        if (empty($video_id[1])) {
            $video_id = explode("/v/", $url); 
        }
            
        $video_id = explode("&", $video_id[1]); 
        $video_id = $video_id[0];

        return $video_id;
    }

    private function youtube($id)
    {
        try {
            $settings = MainSetting::first();
            $this->response = Http::asJson()
            ->get(
                'https://youtube.googleapis.com/youtube/v3/videos',
                [
                    'part' => 'snippet',
                    'id' => $id,
                    'key' => $settings->youtube_api,
                ]
            );
    
            if ($this->response->failed()) {
                toastr()->error(__('Failed to fetch video details for') . ' ' . $id);
                return redirect()->back(); 
            }

            $result = 'Title: ' . $this->response->json('items.0.snippet.title') . '. Description: ' . $this->response->json('items.0.snippet.description');
    
            return $result;

        } catch (Exception $e) {
            toastr()->error(__('Failed to fetch video details for') . ' ' . $e->getMessage());
            return redirect()->back(); 
        }
        
    }

}
