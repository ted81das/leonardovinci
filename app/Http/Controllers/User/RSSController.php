<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Traits\VoiceToneTrait;
use Illuminate\Http\Request;
use OpenAI\Laravel\Facades\OpenAI;
use App\Models\SubscriptionPlan;
use App\Models\Content;
use App\Models\Workbook;
use App\Models\Language;
use App\Models\ApiKey;
use App\Models\User;
use App\Models\BrandVoice;
use App\Services\HelperService;
use App\Models\MainSetting;
use Exception;


class RSSController extends Controller
{
    use VoiceToneTrait;

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
            if ($settings->rss_feature_free_tier) {
                return view('user.rss.index', compact('languages', 'workbooks', 'brands', 'brand_feature'));
            } else {
                toastr()->warning(__('AI RSS feature is not available for free tier users, subscribe to get a proper access'));
                return redirect()->route('user.plans');
            }
        } elseif (auth()->user()->group == 'subscriber') {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($plan->youtube_feature == false) {     
                toastr()->warning(__('Your current subscription plan does not include support for AI RSS feature'));
                return redirect()->back();                   
            } else {
                return view('user.rss.index', compact('languages', 'workbooks', 'brands', 'brand_feature'));
            }
        } else {
            return view('user.rss.index', compact('languages', 'workbooks', 'brands', 'brand_feature'));
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

            if ($request->language == 'en-US') {
                $prompt = 'Create a long article for the following title: ' . $request->titles. '. Break it down into sub section to deliver the message that aligns with the specified title.';
            } else {
                $prompt = "Provide response in " . $flag->language . '.Create a long article for the following title: ' . $request->titles. '. Break it down into sub section to deliver the message that aligns with the specified title.';
            }

            if (isset($request->tone)) {
                $prompt = $prompt . ' \n\n Voice of tone of the article text must be ' . $request->tone . '.';
            }     
            
            if (isset($request->view_point)) {
                if ($request->view_point != 'none')
                    $prompt = $prompt . ' \n\n Rewrite the article in the view point of ' . $request->view_point . ' person. \n\n';
            }
            
            
            $plan_type = (auth()->user()->plan_id) ? 'paid' : 'free';
            
            $content = new Content();
            $content->user_id = auth()->user()->id;
            $content->input_text = $prompt;
            $content->language = $request->language;
            $content->language_name = $flag->language;
            $content->language_flag = $flag->language_flag;
            $content->template_code = $request->code;
            $content->template_name = 'AI RSS';
            $content->icon = '<i class="fa-solid fa-rss rewriter-icon"></i>';
            $content->group = 'rss';
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
	* Update user word balance
	* @param - total words generated
	* @return - confirmation
	*
	*/
    public function updateBalance($words) {

        $user = User::find(Auth::user()->id);

        if (auth()->user()->available_words != -1) {

            if (Auth::user()->available_words > $words) {

                $total_words = Auth::user()->available_words - $words;
                $user->available_words = ($total_words < 0) ? 0 : $total_words;
                $user->update();
    
            } elseif (Auth::user()->available_words_prepaid > $words) {
    
                $total_words_prepaid = Auth::user()->available_words_prepaid - $words;
                $user->available_words_prepaid = ($total_words_prepaid < 0) ? 0 : $total_words_prepaid;
                $user->update();
    
            } elseif ((Auth::user()->available_words + Auth::user()->available_words_prepaid) == $words) {
    
                $user->available_words = 0;
                $user->available_words_prepaid = 0;
                $user->update();
    
            } else {
    
                if (!is_null(Auth::user()->member_of)) {
    
                    $member = User::where('id', Auth::user()->member_of)->first();
    
                    if ($member->available_words > $words) {
    
                        $total_words = $member->available_words - $words;
                        $member->available_words = ($total_words < 0) ? 0 : $total_words;
            
                    } elseif ($member->available_words_prepaid > $words) {
            
                        $total_words_prepaid = $member->available_words_prepaid - $words;
                        $member->available_words_prepaid = ($total_words_prepaid < 0) ? 0 : $total_words_prepaid;
            
                    } elseif (($member->available_words + $member->available_words_prepaid) == $words) {
            
                        $member->available_words = 0;
                        $member->available_words_prepaid = 0;
            
                    } else {
                        $remaining = $words - $member->available_words;
                        $member->available_words = 0;
        
                        $prepaid_left = $member->available_words_prepaid - $remaining;
                        $member->available_words_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                    }
    
                    $member->update();
    
                } else {
                    $remaining = $words - Auth::user()->available_words;
                    $user->available_words = 0;
    
                    $prepaid_left = Auth::user()->available_words_prepaid - $remaining;
                    $user->available_words_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                    $user->update();
                }
            }
        } 

        return true;
    }


    /**
	*
	* Update user word balance
	* @param - total words generated
	* @return - confirmation
	*
	*/
    public function updateBalanceKanji($text) {

        $user = User::find(Auth::user()->id);
  
        $words = mb_strlen($text,'utf8');

        if (Auth::user()->available_words > $words) {

            $total_words = Auth::user()->available_words - $words;
            $user->available_words = ($total_words < 0) ? 0 : $total_words;
            $user->update();

        } elseif (Auth::user()->available_words_prepaid > $words) {

            $total_words_prepaid = Auth::user()->available_words_prepaid - $words;
            $user->available_words_prepaid = ($total_words_prepaid < 0) ? 0 : $total_words_prepaid;
            $user->update();

        } elseif ((Auth::user()->available_words + Auth::user()->available_words_prepaid) == $words) {

            $user->available_words = 0;
            $user->available_words_prepaid = 0;
            $user->update();

        } else {

            if (!is_null(Auth::user()->member_of)) {

                $member = User::where('id', Auth::user()->member_of)->first();

                if ($member->available_words > $words) {

                    $total_words = $member->available_words - $words;
                    $member->available_words = ($total_words < 0) ? 0 : $total_words;
        
                } elseif ($member->available_words_prepaid > $words) {
        
                    $total_words_prepaid = $member->available_words_prepaid - $words;
                    $member->available_words_prepaid = ($total_words_prepaid < 0) ? 0 : $total_words_prepaid;
        
                } elseif (($member->available_words + $member->available_words_prepaid) == $words) {
        
                    $member->available_words = 0;
                    $member->available_words_prepaid = 0;
        
                } else {
                    $remaining = $words - $member->available_words;
                    $member->available_words = 0;
    
                    $prepaid_left = $member->available_words_prepaid - $remaining;
                    $member->available_words_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                }

                $member->update();

            } else {
                $remaining = $words - Auth::user()->available_words;
                $user->available_words = 0;

                $prepaid_left = Auth::user()->available_words_prepaid - $remaining;
                $user->available_words_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                $user->update();
            }
            

        }

        return $words;
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


    public function fetch(Request $request)
    {
        try {

            $rss = simplexml_load_file($request->url);
            $feeds = array();
            $id = 1;
    
            foreach ($rss->channel->item as $item) {
                if ( $id > 20 ) break;
                $feeds[] = [
                    'id' => $id,
                    'link' => $item->link,
                    'title' => $item->title,
                    'description' => $item->description,
                    'image' => $item->enclosure ? $item->enclosure['url'] : null
                ];
                $id++;
            }

            if (!$feeds) {
                return response()->json(["status" => "error", 'message' => __('RSS Not Fetched! Please check your URL and re-validete the RSS URL')]);    
            } else {
                $options = '';

                foreach ($feeds as $post) {
                    $options .= sprintf('<option value="%1$s">%1$s</option>', e($post['title']));
                }

                return response()->json(["status" => "success", 'options' => $options]);
            }
    
        } catch (Exception $e) {
            return $e->getMessage();
        }   
    }

}
