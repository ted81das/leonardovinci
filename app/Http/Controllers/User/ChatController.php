<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use App\Services\Statistics\UserService;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Orhanerday\OpenAi\OpenAi;
use App\Models\SubscriptionPlan;
use App\Models\FavoriteChat;
use App\Models\ChatConversation;
use App\Models\ChatCategory;
use App\Models\ChatHistory;
use App\Models\ChatPrompt;
use App\Models\ApiKey;
use App\Models\CustomChat;
use App\Models\Chat;
use App\Models\User;
use App\Models\BrandVoice;
use App\Models\FineTuneModel;
use App\Models\Setting;
use GuzzleHttp\Client as GuzzleClient;
use App\Services\HelperService;
use WpAi\Anthropic\Facades\Anthropic;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Gemini\Client;
use Michelf\Markdown;
use Exception;


class ChatController extends Controller
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
        if (session()->has('message_code')) {
            session()->forget('message_code');
        }

        $favorite_chats = Chat::select('chats.*', 'favorite_chats.*')->where('favorite_chats.user_id', auth()->user()->id)->join('favorite_chats', 'favorite_chats.chat_code', '=', 'chats.chat_code')->where('status', true)->orderBy('category', 'asc')->get();    
        $user_chats = FavoriteChat::where('user_id', auth()->user()->id)->pluck('chat_code');     
        $other_chats = Chat::whereNotIn('chat_code', $user_chats)->where('status', true)->orderBy('category', 'asc')->get();  
        $original_chat_categories = Chat::where('status', true)->groupBy('group')->pluck('group'); 
        $custom_chat_categories = CustomChat::where('status', true)->groupBy('group')->pluck('group'); 
        $all_categories = $original_chat_categories->merge($custom_chat_categories); 
        $categories = ChatCategory::whereIn('code', $all_categories)->orderBy('name', 'asc')->get();  
        
        $favorite_chats_custom = CustomChat::select('custom_chats.*', 'favorite_chats.*')->where('favorite_chats.user_id', auth()->user()->id)->join('favorite_chats', 'favorite_chats.chat_code', '=', 'custom_chats.chat_code')->where('status', true)->orderBy('category', 'asc')->get();    
        $custom_chats = CustomChat::whereNotIn('chat_code', $user_chats)->where('user_id', auth()->user()->id)->where('type', 'private')->where('status', true)->orderBy('group', 'asc')->get();  
        $public_custom_chats = CustomChat::whereNotIn('chat_code', $user_chats)->where('type', 'custom')->where('status', true)->orderBy('group', 'asc')->get();  
        
        if (!is_null(auth()->user()->plan_id)) {
            $subscription = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            $check = $subscription->personal_chats_feature;
        } else {
            $check = false;
        }
        
        return view('user.chat.index', compact('favorite_chats', 'other_chats', 'categories', 'custom_chats', 'check', 'public_custom_chats', 'favorite_chats_custom'));
    }


    /**
	*
	* Process Input Text
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function process(Request $request) 
    {       
        # Check if user has access to the chat bot
        $template = Chat::where('chat_code', $request->chat_code)->first();
        if (auth()->user()->group == 'user') {
            if (config('settings.chat_feature_user') == 'allow') {
                if (config('settings.chats_access_user') != 'all' && config('settings.chats_access_user') != 'premium') {
                    if (is_null(auth()->user()->member_of)) {
                        if ($template->category == 'professional' && config('settings.chats_access_user') != 'professional') {                       
                            $data['status'] = 'error';
                            $data['message'] = __('This Ai chat assistant is not available for your account, subscribe to get a proper access');
                            return $data;                        
                        } else if($template->category == 'premium' && (config('settings.chats_access_user') != 'premium' && config('settings.chats_access_user') != 'all')) {
                            $data['status'] = 'error';
                            $data['message'] = __('This Ai chat assistant is not available for your account, subscribe to get a proper access');
                            return $data;
                        } else if(($template->category == 'standard' || $template->category == 'all') && (config('settings.chats_access_user') != 'professional' && config('settings.chats_access_user') != 'standard')) {
                            $data['status'] = 'error';
                            $data['message'] = __('This Ai chat assistant is not available for your account, subscribe to get a proper access');
                            return $data;
                        }

                    } else {
                        $user = User::where('id', auth()->user()->member_of)->first();
                        $plan = SubscriptionPlan::where('id', $user->plan_id)->first();
                        if ($plan->chats != 'all' && $plan->chats != 'premium') {          
                            if ($template->category == 'premium' && ($plan->chats != 'premium' && $plan->chats != 'all')) {
                                $status = 'error';
                                $message =  __('Your team subscription does not include support for this chat assistant category');
                                return response()->json(['status' => $status, 'message' => $message]); 
                            } else if(($template->category == 'standard' || $template->category == 'all') && ($plan->chats != 'standard' && $plan->chats != 'all')) {
                                $status = 'error';
                                $message =  __('Your team subscription does not include support for this chat assistant category');
                                return response()->json(['status' => $status, 'message' => $message]); 
                            } else if($template->category == 'professional' && $plan->chats != 'professional') {
                                $status = 'error';
                                $message =  __('Your team subscription does not include support for this chat assistant category');
                                return response()->json(['status' => $status, 'message' => $message]); 
                            }                  
                        }
                    }
                    
                }                
            } else {
                if (is_null(auth()->user()->member_of)) {
                    $status = 'error';
                    $message = __('Ai chat assistant feature is not available for free tier users, subscribe to get a proper access');
                    return response()->json(['status' => $status, 'message' => $message]);
                } else {
                    $user = User::where('id', auth()->user()->member_of)->first();
                    $plan = SubscriptionPlan::where('id', $user->plan_id)->first();
                    if ($plan->chats != 'all' && $plan->chats != 'premium') {          
                        if ($template->category == 'premium' && ($plan->chats != 'premium' && $plan->chats != 'all')) {
                            $status = 'error';
                            $message =  __('Your team subscription does not include support for this chat assistant category');
                            return response()->json(['status' => $status, 'message' => $message]); 
                        } else if(($template->category == 'standard' || $template->category == 'all') && ($plan->chats != 'standard' && $plan->chats != 'all')) {
                            $status = 'error';
                            $message =  __('Your team subscription does not include support for this chat assistant category');
                            return response()->json(['status' => $status, 'message' => $message]); 
                        } else if($template->category == 'professional' && $plan->chats != 'professional') {
                            $status = 'error';
                            $message =  __('Your team subscription does not include support for this chat assistant category');
                            return response()->json(['status' => $status, 'message' => $message]); 
                        }                  
                    }
                }                      
            }
        } elseif (auth()->user()->group == 'subscriber') {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($plan->chats != 'all' && $plan->chats != 'premium') {     
                if ($template->category == 'premium' && ($plan->chats != 'premium' && $plan->chats != 'all')) {
                    $status = 'error';
                    $message =  __('Your current subscription does not include support for this chat assistant category');
                    return response()->json(['status' => $status, 'message' => $message]); 
                } else if(($template->category == 'standard' || $template->category == 'all') && ($plan->chats != 'standard' && $plan->chats != 'all')) {
                    $status = 'error';
                    $message =  __('Your current subscription does not include support for this chat assistant category');
                    return response()->json(['status' => $status, 'message' => $message]); 
                } else if($template->category == 'professional' && $plan->chats != 'professional') {
                    $status = 'error';
                    $message =  __('Your current subscription does not include support for this chat assistant category');
                    return response()->json(['status' => $status, 'message' => $message]); 
                }                   
            }
        }


        # Check personal API keys
        if (config('settings.personal_openai_api') == 'allow') {
            if (is_null(auth()->user()->personal_openai_key)) {
                $status = 'error';
                $message =  __('You must include your personal Openai API key in your profile settings first');
                return response()->json(['status' => $status, 'message' => $message]); 
            }     
        } elseif (!is_null(auth()->user()->plan_id)) {
            $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($check_api->personal_openai_api) {
                if (is_null(auth()->user()->personal_openai_key)) {
                    $status = 'error';
                    $message =  __('You must include your personal Openai API key in your profile settings first');
                    return response()->json(['status' => $status, 'message' => $message]); 
                } 
            }    
        } 


        # Check if user has sufficient words available to proceed
        $verify = HelperService::creditCheck($request->model, 20);
        if (isset($verify['status'])) {
            if ($verify['status'] == 'error') {
                return response()->json(['status' => $verify['status'], 'message' => $verify['message']]);
            }
        }

        $settings = Setting::where('name', 'license')->first(); 
        $uploading = new UserService();
        $upload = $uploading->prompt();
        if($settings->value != $upload['code']){return;}  
        $chat = new ChatHistory();
        $chat->user_id = auth()->user()->id;
        $chat->conversation_id = $request->conversation_id;
        $chat->prompt = $request->input('message');
        $chat->images = $request->image;
        $chat->model = $request->model;
        $chat->save();

        session()->put('conversation_id', $request->conversation_id);
        session()->put('chat_id', $chat->id);
        session()->put('google_search', $request->google_search);
        session()->put('message', $request->input('message'));
        session()->put('company', $request->company);
        session()->put('service', $request->service);

        if (auth()->user()->available_words != -1) {
            //return response()->json(['status' => 'success', 'old'=> $balance, 'current' => ($balance - $words), 'chat_id' => $chat->id]);
            return response()->json(['status' => 'success', 'chat_id' => $chat->id]);
        } else {
            //return response()->json(['status' => 'success', 'old'=> 0, 'current' => 0, 'chat_id' => $chat->id]);
            return response()->json(['status' => 'success', 'chat_id' => $chat->id]);
        }

	}


     /**
	*
	* Process Chat
	* @param - file id in DB
	* @return - confirmation
	*
	*/
    public function generateChat(Request $request) 
    {  
        $conversation_id = $request->conversation_id;

        $message = session()->get('message'); 
        $google_search = session()->get('google_search'); 

        if($google_search == 'on'){ 
         
            $curl = curl_init(); 
            curl_setopt_array($curl, 
                array( 
                    CURLOPT_URL => 'https://google.serper.dev/search', 
                    CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', 
                    CURLOPT_MAXREDIRS => 10, 
                    CURLOPT_TIMEOUT => 0, 
                    CURLOPT_FOLLOWLOCATION => true, 
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, 
                    CURLOPT_CUSTOMREQUEST => 'POST', 
                    CURLOPT_POSTFIELDS => json_encode(["q" => $message]), 
                    CURLOPT_HTTPHEADER => array( 'X-API-KEY: ' . config('services.serper.key'), 'Content-Type: application/json' ), )); 
            $response = curl_exec($curl); 
            curl_close($curl); 

            $responseArray = json_decode($response, true); 
            
            if ($responseArray !== null) { 
            
                $relatedOrganic = isset($responseArray['organic']) ? $responseArray['organic'] : []; 
                $reletedAnswerBox = isset($responseArray['answerBox']) ? $responseArray['answerBox'] : ''; 
                $relatedOrganicString = json_encode($relatedOrganic, JSON_PRETTY_PRINT); 
                $reletedAnswerBox = is_array($reletedAnswerBox) ? json_encode($reletedAnswerBox, JSON_PRETTY_PRINT) : $reletedAnswerBox; $googlePrompt = $relatedOrganicString . "\n\n" . $reletedAnswerBox . "\n\n" . $message . "\n\nGive the answer based on the above Google information or if you will not find the answer on the above information then provide the title along with the links for user to search himself or write the answer from your own way. Do not mention that you are openai or gpt model"; 
            } else { 
                $googlePrompt=''; 
            }

        } else { 
            $googlePrompt = $message; 
        }

        $company = session()->get('company');
        $service = session()->get('service');
        $user_prompt = session()->get('message');
        $prompt = '';

        # Add Brand information
        if ($company != 'none') {
            $brand = BrandVoice::where('id', $company)->first();

            if ($brand) {
                $product = '';
                if ($service != 'none') {
                    foreach($brand->products as $key => $value) {
                        if ($key == $request->service)
                            $product = $value;
                    }
                } 
                
                if ($service != 'none') {
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

        return response()->stream(function () use($conversation_id, $googlePrompt, $prompt, $user_prompt) {

            if (config('settings.personal_openai_api') == 'allow') {
                $open_ai = new OpenAi(auth()->user()->personal_openai_key);        
            } elseif (!is_null(auth()->user()->plan_id)) {
                $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                if ($check_api->personal_openai_api) {
                    $open_ai = new OpenAi(auth()->user()->personal_openai_key);               
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
    
            if (session()->has('chat_id')) {
                $chat_id = session()->get('chat_id');
            }

            $chat_conversation = ChatConversation::where('conversation_id', $conversation_id)->first();  
            $chat_message = ChatHistory::where('id', $chat_id)->first();
            $text = "";
            $model = '';

            $main_chat = Chat::where('chat_code', $chat_conversation->chat_code)->first();
            $chat_messages = ChatHistory::where('conversation_id', $conversation_id)->orderBy('created_at', 'desc')->take(6)->get()->reverse();
            $main_prompt = $main_chat->prompt . ' ' . $prompt;
            $model = $chat_message->model;

            if (is_null($chat_message->images)) {
                
                

                if ($model == 'claude-3-opus-20240229' || $model == 'claude-3-5-sonnet-20240620' || $model == 'claude-3-haiku-20240307') {
                    $messages = [];

                    foreach ($chat_messages as $chat) {
                        $messages[] = ['role' => 'user', 'content' => $chat['prompt']];
                        if (!empty($chat['response'])) {
                            $messages[] = ['role' => 'assistant', 'content' => $chat['response']];
                        } else {
                            $messages[] = ['role' => 'assistant', 'content' => 'Please repeat your question'];
                        }
                     
                    }

                } else {
                    $messages[] = ['role' => 'system', 'content' => $main_prompt];

                    foreach ($chat_messages as $chat) {
                        $messages[] = ['role' => 'user', 'content' => $chat['prompt']];
                        if (!empty($chat['response'])) {
                            $messages[] = ['role' => 'assistant', 'content' => $chat['response']];
                        }
                    }
                }

                if ($googlePrompt != '') {
                    $messages[] = ['role' => 'user', 'content' => $googlePrompt];
                }


                if ($model == 'claude-3-opus-20240229' || $model == 'claude-3-5-sonnet-20240620' || $model == 'claude-3-haiku-20240307') {

                    # Check Claude API key
                    if (config('settings.personal_claude_api') == 'allow') {
                        $claude_api = auth()->user()->personal_claude_key;        
                    } elseif (!is_null(auth()->user()->plan_id)) {
                        $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                        if ($check_api->personal_claude_api) {
                            $claude_api = auth()->user()->personal_claude_key;               
                        } else {
                            $claude_api = config('anthropic.api_key');                           
                       }                       
                    } else {
                        $claude_api = config('anthropic.api_key'); 
                    }

                    $anthropic = new \WpAi\Anthropic\AnthropicAPI($claude_api);

                    try {
                        $response = $anthropic->messages()
                                    ->model($model)
                                    ->maxTokens(4096)
                                    ->system($main_prompt)
                                    ->messages($messages)
                                    ->temperature(1.0)
                                    ->stream();

                        foreach ($response as $result) {
                            if ($result['type'] == 'content_block_delta') {
                                $raw = $result['delta']['text'];

                            // $clean = str_replace(["\r\n", "\r", "\n"], "<br/>", $raw);
                                $text .= $raw;

                                echo 'data: ' . $raw ."\n\n";
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
                    

                } elseif ($model == 'gemini_pro') {

                    # Check Gemini API key
                    if (config('settings.personal_gemini_api') == 'allow') {
                        $gemini_api = auth()->user()->personal_gemini_key;        
                    } elseif (!is_null(auth()->user()->plan_id)) {
                        $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                        if ($check_api->personal_gemini_api) {
                            $gemini_api = auth()->user()->personal_gemini_key;               
                        } else {
                            $gemini_api = config('gemini.api_key');                           
                       }                       
                    } else {
                        $gemini_api = config('gemini.api_key'); 
                    }

                    $gemini_client = \Gemini::factory()
                        ->withApiKey($gemini_api)
                        ->withHttpClient($client = new GuzzleClient())
                        ->withStreamHandler(fn (RequestInterface $request): ResponseInterface => $client->send($request, ['stream' => true]))
                      ->make();

                    try {
                        $prompt = $main_prompt . ' Based on previous information about your role, answer this users question: ' . $user_prompt; 
                        //$clean = Markdown::defaultTransform($response->text());
                        $stream = $gemini_client->geminiPro()->streamGenerateContent($prompt);

                        foreach ($stream as $response) {
                           $clean = str_replace(["\r\n", "\r", "\n"], "<br/>", $response->text());
                           $text .= $response->text();
                           echo 'data: ' . $clean ."\n\n";
                           ob_flush();
                           flush();
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
   
                } else {

                    $opts = [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => 1.0,
                        'frequency_penalty' => 0,
                        'presence_penalty' => 0,
                        'stream' => true
                    ];                

                    try {

                        $complete = $open_ai->chat($opts, function ($curl_info, $data) use (&$text) {
                            if ($obj = json_decode($data) and $obj->error->message != "") {
                                \Log::info(json_encode($obj->error->message));
                                echo "data: " . $obj->error->message;
                                echo "\n\n";
                                ob_flush();
                                flush();
                                echo 'data: [DONE]';
                                echo "\n\n";
                                ob_flush();
                                flush();
                                usleep(50000);
                            } else {
                                echo $data;
        
                                $array = explode('data: ', $data);
                                foreach ($array as $response){
                                    $response = json_decode($response, true);

                                    if ($data != "data: [DONE]\n\n" and isset($response["choices"][0]["delta"]["content"])) {
                                        $text .= $response["choices"][0]["delta"]["content"];
                                    }
                                }
                            }
        
                            echo PHP_EOL;
                            ob_flush();
                            flush();
                            return strlen($data);
                        });

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
                }
                

            } else {
                $guzzle_client = new GuzzleClient();
                $url = 'https://api.openai.com/v1/chat/completions';
                
                if ($model == 'claude-3-opus-20240229' || $model == 'claude-3-5-sonnet-20240620' || $model == 'claude-3-haiku-20240307' || $model == 'gemini_pro') {
                    $model = 'gpt-4o-mini';
                }
                
                $response = $guzzle_client->post($url,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . config('services.openai.key'),
                    ],
                    'json' => [
                        'model' => $model,
                        'messages' => [
                            [
                            'role' => 'user',
                            'content' => [
                                        [
                                            'type' => 'text',
                                            'text' => $chat_message->prompt,
                                        ],
                                        [
                                        'type' => 'image_url',
                                        'image_url' => [
                                            'url' => $chat_message->images,
                                            ],
                                        ],
                                ],
                            ],
                        ],
                        'max_tokens' => 2500,
                        'stream' => true,
                        
                    ]
                ]);     

                foreach (explode("\n", $response->getBody()->getContents()) as $data) { 
                    if ($data != 'data: [DONE]') {
                        $array = explode('data: ', $data);
                    } else {
                        echo "data: [DONE]";
                    }
                    
                    foreach ($array as $response){
                        $response = json_decode($response, true);
                        if ($data != "data: [DONE]\n\n" and isset($response["choices"][0]["delta"]["content"])) {
                            $text .= $response["choices"][0]["delta"]["content"];
                            $raw = $response['choices'][0]['delta']['content'];
                            $clean = str_replace(["\r\n", "\r", "\n"], "<br/>", $raw);
                            echo "data: " . $clean;
                        }
                    }
                
                    echo PHP_EOL;
                    ob_flush();
                    flush();
                    
                }
            }

            # Update credit balance
            $words = count(explode(' ', ($text)));
            HelperService::updateBalance($words, $model);  

            $current_chat = ChatHistory::where('id', $chat_id)->first();
            $current_chat->response = $text;
            $current_chat->words = $words;
            $current_chat->save();

            $chat_conversation->words = ++$words;
            $chat_conversation->messages = $chat_conversation->messages + 1;
            $chat_conversation->save();

        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
        ]);
        
    }


    /**
	*
	* Process Input Text
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function processCustom(Request $request) 
    {    
        # Check personal API keys
        if (config('settings.personal_openai_api') == 'allow') {
            if (is_null(auth()->user()->personal_openai_key)) {
                $status = 'error';
                $message =  __('You must include your personal Openai API key in your profile settings first');
                return response()->json(['status' => $status, 'message' => $message]); 
            }     
        } elseif (!is_null(auth()->user()->plan_id)) {
            $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($check_api->personal_openai_api) {
                if (is_null(auth()->user()->personal_openai_key)) {
                    $status = 'error';
                    $message =  __('You must include your personal Openai API key in your profile settings first');
                    return response()->json(['status' => $status, 'message' => $message]); 
                } 
            }    
        } 


        # Check if user has sufficient words available to proceed
        $verify = HelperService::creditCheck($request->model, 20);
        if (isset($verify['status'])) {
            if ($verify['status'] == 'error') {
                return response()->json(['status' => $verify['status'], 'message' => $verify['message']]);
            }
        }

        $settings = Setting::where('name', 'license')->first(); 
        $uploading = new UserService();
        $upload = $uploading->prompt();
        if($settings->value != $upload['code']){return;} 
        $chat = new ChatHistory();
        $chat->user_id = auth()->user()->id;
        $chat->conversation_id = $request->conversation_id;
        $chat->prompt = $request->input('message');
        $chat->images = $request->image;
        $chat->model = $request->model;
        $chat->save();

        session()->put('conversation_id', $request->conversation_id);
        session()->put('chat_id', $chat->id);
        session()->put('google_search', $request->google_search);
        session()->put('message', $request->input('message'));
        session()->put('company', $request->company);
        session()->put('service', $request->service);

        if (auth()->user()->available_words != -1) {
            //return response()->json(['status' => 'success', 'old'=> $balance, 'current' => ($balance - $words), 'chat_id' => $chat->id]);
            return response()->json(['status' => 'success', 'chat_id' => $chat->id]);
        } else {
           //return response()->json(['status' => 'success', 'old'=> 0, 'current' => 0, 'chat_id' => $chat->id]);
            return response()->json(['status' => 'success', 'chat_id' => $chat->id]);
        }

	}


     /**
	*
	* Process Custom Chat
	* @param - file id in DB
	* @return - confirmation
	*
	*/
    public function generateCustomChat(Request $request) 
    {  
        $conversation_id = $request->conversation_id;

        $message = session()->get('message'); 
        $google_search = session()->get('google_search'); 

        if($google_search == 'on'){ 
         
            $curl = curl_init(); 
            curl_setopt_array($curl, 
                array( 
                    CURLOPT_URL => 'https://google.serper.dev/search', 
                    CURLOPT_RETURNTRANSFER => true, CURLOPT_ENCODING => '', 
                    CURLOPT_MAXREDIRS => 10, 
                    CURLOPT_TIMEOUT => 0, 
                    CURLOPT_FOLLOWLOCATION => true, 
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, 
                    CURLOPT_CUSTOMREQUEST => 'POST', 
                    CURLOPT_POSTFIELDS => json_encode(["q" => $message]), 
                    CURLOPT_HTTPHEADER => array( 'X-API-KEY: ' . config('services.serper.key'), 'Content-Type: application/json' ), )); 
            $response = curl_exec($curl); 
            curl_close($curl); 

            $responseArray = json_decode($response, true); 
            
            if ($responseArray !== null) { 
            
                $relatedOrganic = isset($responseArray['organic']) ? $responseArray['organic'] : []; 
                $reletedAnswerBox = isset($responseArray['answerBox']) ? $responseArray['answerBox'] : ''; 
                $relatedOrganicString = json_encode($relatedOrganic, JSON_PRETTY_PRINT); 
                $reletedAnswerBox = is_array($reletedAnswerBox) ? json_encode($reletedAnswerBox, JSON_PRETTY_PRINT) : $reletedAnswerBox; $googlePrompt = $relatedOrganicString . "\n\n" . $reletedAnswerBox . "\n\n" . $message . "\n\nGive the answer based on the above Google information or if you will not find the answer on the above information then provide the title along with the links for user to search himself or write the answer from your own way. Do not mention that you are openai or gpt model"; 
            } else { 
                $googlePrompt=''; 
            }

        } else { 
            $googlePrompt = $message; 
        }

        $model = session()->get('model');
        $company = session()->get('company');
        $service = session()->get('service');
        $prompt = '';

        # Add Brand information
        if ($company != 'none') {
            $brand = BrandVoice::where('id', $company)->first();

            if ($brand) {
                $product = '';
                if ($service != 'none') {
                    foreach($brand->products as $key => $value) {
                        if ($key == $request->service)
                            $product = $value;
                    }
                } 
                
                if ($service != 'none') {
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

        return response()->stream(function () use($conversation_id, $googlePrompt, $prompt) {

            if (config('settings.personal_openai_api') == 'allow') {
                $open_ai = auth()->user()->personal_openai_key;        
            } elseif (!is_null(auth()->user()->plan_id)) {
                $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                if ($check_api->personal_openai_api) {
                    $open_ai = auth()->user()->personal_openai_key;               
                } else {
                    if (config('settings.openai_key_usage') !== 'main') {
                       $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                       array_push($api_keys, config('services.openai.key'));
                       $key = array_rand($api_keys, 1);
                       $open_ai = $api_keys[$key];
                   } else {
                       $open_ai = config('services.openai.key');
                   }
               }
               
            } else {
                if (config('settings.openai_key_usage') !== 'main') {
                    $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                    array_push($api_keys, config('services.openai.key'));
                    $key = array_rand($api_keys, 1);
                    $open_ai = $api_keys[$key];
                } else {
                    $open_ai = config('services.openai.key');
                }
            }
    
            if (session()->has('chat_id')) {
                $chat_id = session()->get('chat_id');
            }

            if (session()->has('message')) {
                $message = session()->get('message');
            }

            $chat_conversation = ChatConversation::where('conversation_id', $conversation_id)->first();  
            $chat_message = ChatHistory::where('id', $chat_id)->first();
            $text = "";
                
            $main_chat = CustomChat::where('chat_code', $chat_conversation->chat_code)->first();

            if ($googlePrompt != '') {
                $messages[] = ['role' => 'user', 'content' => $googlePrompt];
            }

            if(request()->has('file')) {
                $file = request()->file('file');

                $imageTypes = ['c', 'cpp', 'doc', 'docx', 'html', 'java', 'md', 'php', 'pptx', 'py', 'rb', 'tex', 'js', 'ts', 'pdf', 'txt', 'json'];
                if (!in_array(Str::lower($file->getClientOriginalExtension()), $imageTypes)) {
                    toastr()->error(__('Unsupported file format was selected, make sure to upload a file with a supported file format listed below'));
                    return redirect()->back();

                } else {

                    $name = Str::random(20);            
                    $folder = '/uploads/assistant/';            
                    $filePath = $folder . $name . '.' . $file->getClientOriginalExtension();
                    $this->uploadImage($file, $folder, 'public', $name);

                    $client = \OpenAI::factory()
                    ->withApiKey($open_ai)
                    ->withHttpHeader('OpenAI-Beta', 'assistants=v2')
                    ->make();

                    $uploaded_file = $client->files()->upload([
                        'purpose' => 'assistants',
                        'file' => fopen( public_path() . $filePath, 'rb'),
                    ]);

                    $this->addFile($open_ai, $chat_conversation->vector_store,  $uploaded_file['id']);

                    $this->modifyThread($open_ai, $conversation_id, $chat_conversation->vector_store);

                }
            } 

            $latest_message = [
                'role' => 'user',
                'content' => $message,
            ];

            $this->createMessage($open_ai, $conversation_id, $latest_message);

            $client = HttpClient::create();
            $client = new EventSourceHttpClient($client, reconnectionTime: 2);

            $url = 'https://api.openai.com/v1/threads/' . $conversation_id . '/runs';

            $headers = [
                'OpenAI-Beta' => 'assistants=v2',
                'Authorization' => 'Bearer ' . $open_ai,
                'Content-Type' => 'application/json',
            ];

            $body = json_encode([
                'assistant_id' => $chat_conversation->chat_code,
                    'model' => $chat_message->model,
                    'stream' => true 
            ]);

            try {
                $source = $client->request(
                    method: 'POST',
                    url: $url,
                    options: [                  
                        'buffer' => false,
                        'headers' => $headers,
                        'body' => $body,
                    ],
                );
    
                while ($source) {
                    foreach ($client->stream($source) as $chunk) {
                        if ($chunk instanceof ServerSentEvent) {
                            
                            $raw = $chunk->getArrayData();
    
                            if ($raw['object'] == 'thread.message.delta') {
                                $answer = $raw['delta']['content'][0]['text']['value'];
                                $text .= $answer;
                                $clean = str_replace(["\r\n", "\r", "\n"], "<br/>", $answer);
                                echo "data: " . $clean;
                                echo "\n\n";
                                ob_flush();
                                flush();                                

                            } elseif ($raw['object'] == 'thread.run') {
                                if ($raw['status'] == 'completed')
                                    break; 
                            }
                           
                        } else {
                            break;
                        }
                    }
                }

                echo 'data: [DONE]';
                echo "\n\n";
                ob_flush();
                flush();

            } catch (Exception $e) {
                echo 'data: [DONE]';
                echo "\n\n";
                ob_flush();
                flush();
            }
                

            # Update credit balance
            $words = count(explode(' ', ($text)));
            HelperService::updateBalance($words, $main_chat->model);  

            $current_chat = ChatHistory::where('id', $chat_id)->first();
            $current_chat->response = $text;
            $current_chat->words = $words;
            $current_chat->save();

            $chat_conversation->words = ++$words;
            $chat_conversation->messages = $chat_conversation->messages + 1;
            $chat_conversation->save();

        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
        ]);
        
    }


    /**
	*
	* Clear Session
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function clear(Request $request) 
    {
        if (session()->has('conversation_id')) {
            session()->forget('conversation_id');
        }

        return response()->json(['status' => 'success']);
	}



    /**
	*
	* Chat conversation
	* @param - total words generated
	* @return - confirmation
	*
	*/
    public function conversation(Request $request) {

        if ($request->ajax()) {

            if (isset($request->custom)) {
                
                if (config('settings.personal_openai_api') == 'allow') {
                    $open_ai = auth()->user()->personal_openai_key;        
                } elseif (!is_null(auth()->user()->plan_id)) {
                    $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                    if ($check_api->personal_openai_api) {
                        $open_ai = auth()->user()->personal_openai_key;               
                    } else {
                        if (config('settings.openai_key_usage') !== 'main') {
                        $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                        array_push($api_keys, config('services.openai.key'));
                        $key = array_rand($api_keys, 1);
                        $open_ai = $api_keys[$key];
                    } else {
                        $open_ai = config('services.openai.key');
                    }
                }
                
                } else {
                    if (config('settings.openai_key_usage') !== 'main') {
                        $api_keys = ApiKey::where('engine', 'openai')->where('status', true)->pluck('api_key')->toArray();
                        array_push($api_keys, config('services.openai.key'));
                        $key = array_rand($api_keys, 1);
                        $open_ai = $api_keys[$key];
                    } else {
                        $open_ai = config('services.openai.key');
                    }
                }

                $response = $this->createThread($open_ai);

                $vector = $this->createVectorStore($open_ai);

                $chat = new ChatConversation();
                $chat->user_id = auth()->user()->id;
                $chat->title = 'New Conversation';
                $chat->chat_code = $request->chat_code;
                $chat->conversation_id = $response['id'];
                $chat->messages = 0;
                $chat->words = 0;
                $chat->vector_store = $vector['id'];
                $chat->save();

                $data['status'] = 'success';
                $data['id'] = $response['id'];
                return $data;
            } else {
                $chat = new ChatConversation();
                $chat->user_id = auth()->user()->id;
                $chat->title = 'New Conversation';
                $chat->chat_code = $request->chat_code;
                $chat->conversation_id = $request->conversation_id;
                $chat->messages = 0;
                $chat->words = 0;
                $chat->save();

                $data = 'success';
                return $data;
            }

            
        }   
    }


    /**
	*
	* Chat history
	* @param - total words generated
	* @return - confirmation
	*
	*/
    public function history(Request $request) {

        if ($request->ajax()) {

            $messages = ChatHistory::where('user_id', auth()->user()->id)->where('conversation_id', $request->conversation_id)->get();
            return $messages;
        }   
    }


    /**
	* 
	* Process media file
	* @param - file id in DB
	* @return - confirmation
	* 
	*/
	public function view($code) 
    {
        if (session()->has('conversation_id')) {
            session()->forget('conversation_id');
        }

        $chat = Chat::where('chat_code', $code)->first(); 
        $messages = ChatConversation::where('user_id', auth()->user()->id)->where('chat_code', $chat->chat_code)->orderBy('updated_at', 'desc')->get(); 

        $categories = ChatPrompt::where('status', true)->groupBy('group')->pluck('group'); 
        $prompts = ChatPrompt::where('status', true)->get();

        if (!is_null(auth()->user()->plan_id)) {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            $internet = $plan->internet_feature;
        } else {
            if (config('settings.internet_user_access') == 'allow') {
                $internet = true;
            } else {
                $internet = false;
            }
        }

        $default_model = auth()->user()->default_model_chat;
        $brands = BrandVoice::where('user_id', auth()->user()->id)->get();
        $brands_feature = \App\Services\HelperService::checkBrandsFeature();

        return view('user.chat.view', compact('chat', 'messages', 'categories', 'prompts', 'internet', 'brands', 'brands_feature', 'default_model'));
	}


    public function viewCustom($code) 
    {
        if (session()->has('conversation_id')) {
            session()->forget('conversation_id');
        }

        $chat = CustomChat::where('chat_code', $code)->first(); 
        $messages = ChatConversation::where('user_id', auth()->user()->id)->where('chat_code', $chat->chat_code)->orderBy('updated_at', 'desc')->get(); 

        $categories = ChatPrompt::where('status', true)->groupBy('group')->pluck('group'); 
        $prompts = ChatPrompt::where('status', true)->get();

        if (!is_null(auth()->user()->plan_id)) {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            $internet = $plan->internet_feature;
        } else {
            if (config('settings.internet_user_access') == 'allow') {
                $internet = true;
            } else {
                $internet = false;
            }
        } 

        # Apply proper model based on role and subsciption
        if (auth()->user()->group == 'user') {
            $models = explode(',', config('settings.free_tier_models'));
        } elseif (!is_null(auth()->user()->plan_id)) {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            $models = explode(',', $plan->model_chat);
        } else {            
            $models = explode(',', config('settings.free_tier_models'));
        }

        $default_model = auth()->user()->default_model_chat;
        $fine_tunes = FineTuneModel::all();
        $brands = BrandVoice::where('user_id', auth()->user()->id)->get();
        $brands_feature = \App\Services\HelperService::checkBrandsFeature();

        return view('user.chat.view-custom', compact('chat', 'messages', 'categories', 'prompts', 'internet', 'brands', 'models', 'fine_tunes', 'brands_feature', 'default_model'));
	}


    /**
	*
	* Rename conversation
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function rename(Request $request) 
    {
        if ($request->ajax()) {

            $chat = ChatConversation::where('conversation_id', request('conversation_id'))->first(); 

            if ($chat) {
                if ($chat->user_id == auth()->user()->id){

                    $chat->title = request('name');
                    $chat->save();
    
                    $data['status'] = 'success';
                    $data['conversation_id'] = request('conversation_id');
                    return $data;  
        
                } else{
    
                    $data['status'] = 'error';
                    $data['message'] = __('There was an error while changing the conversation title');
                    return $data;
                }
            } 

        }
	}


    /**
	*
	* Rename conversation
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function listen(Request $request) 
    {
        if ($request->ajax()) {

            $voice = config('settings.chat_default_voice');

            # Count characters 
            $total_characters = mb_strlen($request->text, 'UTF-8');

            # Check if user has characters available to proceed
            if (auth()->user()->available_chars != -1) {
                if ((Auth::user()->available_chars + Auth::user()->available_chars_prepaid) < $total_characters) {
                    $data['status'] = 'error';
                    $data['message'] = __('Not sufficient characters to generate audio, please subscribe or top up');
                    return $data;
                } else {
                    $this->updateAvailableCharacters($total_characters);
                } 
            }

            if (config('settings.personal_openai_api') == 'allow') {
                if (is_null(auth()->user()->personal_openai_key)) {
                    $data['status'] = 'error';
                    $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                    return $data; 
                } else {
                    config(['openai.api_key' => auth()->user()->personal_openai_key]); 
                } 
    
            } elseif (!is_null(auth()->user()->plan_id)) {
                $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                if ($check_api->personal_openai_api) {
                    if (is_null(auth()->user()->personal_openai_key)) {
                        $data['status'] = 'error';
                        $data['message'] = __('You must include your personal Openai API key in your profile settings first');
                        return $data; 
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


            try {
                $audio_stream = \OpenAI\Laravel\Facades\OpenAI::audio()->speech([
                    'model' => 'tts-1',
                    'input' => $request->text,
                    'voice' => $voice,
                ]);

                $file_name = 'chat-audio-' . Str::random(10) . '.mp3';

                if (config('settings.voiceover_default_storage') == 'aws') {
                    Storage::disk('s3')->put('chat/' . $file_name, $audio_stream, 'public');                
                    $result_url = Storage::disk('s3')->url('chat/' . $file_name);  
                } elseif (config('settings.voiceover_default_storage') == 'wasabi') {
                    Storage::disk('wasabi')->put('chat/' . $file_name, $audio_stream, 'public');                
                    $result_url = Storage::disk('wasabi')->url('chat/' . $file_name);        
                } elseif (config('settings.voiceover_default_storage') == 'r2') {
                    Storage::disk('r2')->put('chat/' . $file_name, $audio_stream, 'public');                
                    $result_url = Storage::disk('r2')->url('chat/' . $file_name);            
                } else {     
                    Storage::disk('audio')->put($file_name, $audio_stream);            
                    $result_url = Storage::url($file_name);                
                }


                $data['status'] = 'success';
                $data['url'] = $result_url; 
                return $data;

            } catch(Exception $e) {
                $data['status'] = 'error';
                $data['message'] = __('There was an error while generating audio, please contact support') . $e->getMessage();
                return $data;
            }

            
    
            

              
        }
	}


    /**
	*
	* Delete chat
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function delete(Request $request) 
    {
        if ($request->ajax()) {

            $chat = ChatConversation::where('conversation_id', request('conversation_id'))->first(); 

            if ($chat) {
                if ($chat->user_id == auth()->user()->id){

                    $chat->delete();

                    if (session()->has('conversation_id')) {
                        session()->forget('conversation_id');
                    }
    
                    $data['status'] = 'success';
                    return $data;  
        
                } else{
    
                    $data['status'] = 'error';
                    $data['message'] = __('There was an error while deleting the chat history');
                    return $data;
                }
            } else {
                $data['status'] = 'empty';
                return $data;
            }
              
        }
	}


    public function model(Request $request) 
    {
        if ($request->ajax()) {

            switch ($request->model) {
                case 'gpt-3.5-turbo-0125':
                    $model = 'GPT 3.5 Turbo';
                    $balance = (auth()->user()->gpt_3_turbo_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableWords();
                    break;
                case 'gpt-4':
                    $model = 'GPT 4';
                    $balance = (auth()->user()->gpt_4_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableGPT4Words();
                    break;
                case 'gpt-4o':
                    $model = 'GPT 4o';
                    $balance = (auth()->user()->gpt_4o_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableGPT4oWords();
                    break;
                case 'gpt-4-0125-preview':
                    $model = 'GPT 4 Turbo';
                    $balance = (auth()->user()->gpt_4_turbo_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableGPT4TWords();
                    break;            
                case 'gpt-4-turbo-2024-04-09':
                    $model = 'GPT 4 Turbo Vision';
                    $balance = (auth()->user()->gpt_4_turbo_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableGPT4TWords();
                    break;
                case 'claude-3-opus-20240229':
                    $model = 'Claude 3 Opus';
                    $balance = (auth()->user()->claude_3_opus_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableClaudeOpusWords();
                    break;
                case 'claude-3-5-sonnet-20240620':
                    $model = 'Claude 3.5 Sonnet';
                    $balance = (auth()->user()->claude_3_sonnet_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableClaudeSonnetWords();
                    break;
                case 'claude-3-haiku-20240307':
                    $model = 'Claude 3 Haiku';
                    $balance = (auth()->user()->claude_3_haiku_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableClaudeHaikuWords();
                    break;
                case 'gemini_pro':
                    $model = 'Gemini Pro';
                    $balance = (auth()->user()->gemini_pro_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableGeminiProWords();
                    break;
                default:
                    $model = 'Fine Tune';
                    $balance = (auth()->user()->fine_tune_credits == -1) ? __('Unlimited') : \App\Services\HelperService::userAvailableFineTuneWords();
                    break;
            } 

            $data['model'] = $model;
            $data['balance'] = $balance;
            return $data; 
              
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


            if (strlen(request('id')) < 6) {
                $chat = Chat::where('chat_code', request('id'))->first(); 
            } else {
                $chat = CustomChat::where('chat_code', request('id'))->first();
            }

            $favorite = FavoriteChat::where('chat_code', $chat->chat_code)->where('user_id', auth()->user()->id)->first();

            if ($favorite) {

                $favorite->delete();

                $data['status'] = 'success';
                $data['set'] = true;
                return $data;  
    
            } else{

                $new_favorite = new FavoriteChat();
                $new_favorite->user_id = auth()->user()->id;
                $new_favorite->chat_code = $chat->chat_code;
                $new_favorite->save();

                $data['status'] = 'success';
                $data['set'] = false;
                return $data; 
            }  
        }
	}


    public function escapeJson($value) 
    { 
        $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
        $result = str_replace($escapers, $replacements, $value);
        return $result;
    }


    /**
     * Update user characters number
     */
    private function updateAvailableCharacters($characters)
    {
        $user = User::find(Auth::user()->id);

        if (auth()->user()->available_chars != -1) {
            
            if (Auth::user()->available_chars > $characters) {

                $total_chars = Auth::user()->available_chars - $characters;
                $user->available_chars = ($total_chars < 0) ? 0 : $total_chars;

            } elseif (Auth::user()->available_chars_prepaid > $characters) {

                $total_chars_prepaid = Auth::user()->available_chars_prepaid - $characters;
                $user->available_chars_prepaid = ($total_chars_prepaid < 0) ? 0 : $total_chars_prepaid;

            } elseif ((Auth::user()->available_chars + Auth::user()->available_chars_prepaid) == $characters) {

                $user->available_chars = 0;
                $user->available_chars_prepaid = 0;

            } else {

                if (!is_null(Auth::user()->member_of)) {

                    $member = User::where('id', Auth::user()->member_of)->first();

                    if ($member->available_chars > $characters) {

                        $total_chars = $member->available_chars - $characters;
                        $member->available_chars = ($total_chars < 0) ? 0 : $total_chars;
            
                    } elseif ($member->available_words_prepaid > $characters) {
            
                        $total_chars_prepaid = $member->available_chars_prepaid - $characters;
                        $member->available_chars_prepaid = ($total_chars_prepaid < 0) ? 0 : $total_chars_prepaid;
            
                    } elseif (($member->available_chars + $member->available_chars_prepaid) == $characters) {
            
                        $member->available_chars = 0;
                        $member->available_chars_prepaid = 0;
            
                    } else {
                        $remaining = $characters - $member->available_chars;
                        $member->available_chars = 0;
        
                        $prepaid_left = $member->available_chars_prepaid - $remaining;
                        $member->available_chars_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                    }

                    $member->update();

                } else {

                    $remaining = $characters - Auth::user()->available_chars;
                    $user->available_chars = 0;

                    $used = Auth::user()->available_chars_prepaid - $remaining;
                    $user->available_chars_prepaid = ($used < 0) ? 0 : $used;
                }
            }
        }

        $user->update();
    }


    
    public function createVectorStore($openai)
    {
        $url = 'https://api.openai.com/v1/vector_stores';

        $ch = curl_init();

        $data = array(
            "name" => "Chatbot Assistant",
        ); 
                    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));   
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2',
            'Authorization: Bearer ' . $openai,
        )); 

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result , true);

        return $response;
    }


    public function addFile($openai, $vector_store_id, $file_id)
    {
        $url = 'https://api.openai.com/v1/vector_stores/' . $vector_store_id . '/files';

        $ch = curl_init();

        $data = array(
            "file_id" => $file_id,
        ); 
                    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));   
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2',
            'Authorization: Bearer ' . $openai,
        )); 

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result , true);

        return $response;
    }


    public function createThread($openai)
    {
        $url = 'https://api.openai.com/v1/threads';

        $ch = curl_init();
                    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true); 
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2',
            'Authorization: Bearer ' . $openai,
        )); 

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result , true);

        return $response;

    }


    public function modifyThread($openai, $thread_id, $vector_id)
    {
        $url = 'https://api.openai.com/v1/threads/' . $thread_id;

        $ch = curl_init();

        $data = array(
            "tool_resources" => ["file_search" => ["vector_store_ids" => [$vector_id],]]
        ); 
                 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));   
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2',
            'Authorization: Bearer ' . $openai,
        )); 

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result , true);

        return $response;

    }


    public function createMessage($openai, $thread_id, $messages)
    {
        $url = 'https://api.openai.com/v1/threads/' . $thread_id . '/messages';

        $ch = curl_init();
                 
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messages));   
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2',
            'Authorization: Bearer ' . $openai,
        )); 

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result , true);

        return $response;

    }

}
