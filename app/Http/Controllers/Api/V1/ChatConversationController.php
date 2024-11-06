<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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


class ChatConversationController extends Controller
{

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

        $user_prompt = session()->get('message');
        $prompt = '';


        return response()->stream(function () use($conversation_id, $prompt, $user_prompt) {

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

            if ($model == 'claude-3-opus-20240229' || $model == 'claude-3-sonnet-20240229' || $model == 'claude-3-haiku-20240307') {
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


            if ($model == 'claude-3-opus-20240229' || $model == 'claude-3-sonnet-20240229' || $model == 'claude-3-haiku-20240307') {

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

        return response()->stream(function () use($conversation_id) {

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
     * Create chat conversation
     *
     * @OA\Post(
     *      path="/api/v1/chat/conversation",
     *      operationId="createConversation",
     *      tags={"AI Chat (Conversation)"},
     *      summary="Create Chat Conversation",
     *      description="Create chat conversation",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="chat_code",
     *          in="path",
     *          description="Chat code for which you want to create a new conversation",
     *          required=true,
     *          @OA\Schema(type="string", example="TKDRY"),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="boolean", description="Creation status"),
     *              @OA\Property(property="conversation_id", type="integer", description="Conversation ID"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Chatbot Not Found",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
    public function conversation(Request $request) 
    {
        if($request->chat_code == null) {
            return response()->json(['error' => __('Chat code is missing.')], 412);
        }

        if (strlen(request('chat_code')) > 6) {
                
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

            return response()->json(['status' => true, 'conversation_id' => $response['id']], 201); 

        } else {
            $conversation_id = Str::random(10);
            $chat = new ChatConversation();
            $chat->user_id = auth()->user()->id;
            $chat->title = 'New Conversation';
            $chat->chat_code = $request->chat_code;
            $chat->conversation_id = $conversation_id;
            $chat->messages = 0;
            $chat->words = 0;
            $chat->save();

            return response()->json(['status' => true, 'conversation_id' => $conversation_id], 201); 
        }
  
    }


    /**
     * Get messages of a conversation
     *
     * @OA\Get(
     *      path="/api/v1/chat/conversation/{conversation_id}/messages",
     *      operationId="getMessages",
     *      tags={"AI Chat (Conversation)"},
     *      summary="Get messages of a conversation",
     *      description="Show all messages that belongs to a particular conversation ID",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="conversation_id",
     *          in="path",
     *          description="ID of the target conversation",
     *          required=true,
     *          @OA\Schema(type="string", example="TKDRYWDASE"),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="messages", type="object", description="All messages stored under this conversation ID for this user"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Messages Not Found",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
    public function messages(Request $request, String $conversation_id) {
        
        if($conversation_id == null) {
            return response()->json(['error' => __('Conversation ID is missing.')], 412);
        }

        $messages = ChatHistory::where('user_id', auth()->user()->id)->where('conversation_id', $conversation_id)->get();
        return response()->json(['messages' => $messages], 200);
    }


    /**
     * Get a particular message information
     *
     * @OA\Get(
     *      path="/api/v1/chat/conversation/{conversation_id}/messages/{message_id}",
     *      operationId="getMessage",
     *      tags={"AI Chat (Conversation)"},
     *      summary="Get a particular message info from the conversation",
     *      description="Show a detailed message information based on its message id in a conversation",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="conversation_id",
     *          in="path",
     *          description="ID of the target conversation",
     *          required=true,
     *          @OA\Schema(type="string", example="TKDRYWDASE"),
     *      ),
     *      @OA\Parameter(
     *          name="message_id",
     *          in="path",
     *          description="ID of the message",
     *          required=true,
     *          @OA\Schema(type="string", example="1"),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="object", description="Detailed message information"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Message Not Found",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
    public function message(Request $request, String $conversation_id, String $message_id) {
        
        if($conversation_id == null) {
            return response()->json(['error' => __('Conversation ID is missing.')], 412);
        }

        $message = ChatHistory::where('user_id', auth()->user()->id)->where('conversation_id', $conversation_id)->where('id', $message_id)->get();
        if ($message) {
            return response()->json(['messages' => $message], 200);
        } else {
            return response()->json(['error' => __('Message not found.')], 404);
        }
       
    }



    /**
     * Rename chat conversation
     *
     * @OA\Post(
     *      path="/api/v1/chat/conversation/rename",
     *      operationId="renameConversation",
     *      tags={"AI Chat (Conversation)"},
     *      summary="Rename a Chat Conversation",
     *      description="Rename a chat conversation",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="conversation_id",
     *          in="path",
     *          description="ID of the target conversation",
     *          required=true,
     *          @OA\Schema(type="string", example="TKDRYWDASE"),
     *      ),
     *      @OA\Parameter(
     *          name="name",
     *          in="path",
     *          description="Title name for the chat conversation",
     *          required=true,
     *          @OA\Schema(type="string", example="Bot Advice for an Exam"),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Conversation Not Found",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
	public function rename(Request $request) 
    {
        if($request->conversation_id == null) {
            return response()->json(['error' => __('Conversation ID is missing.')], 412);
        }

        if($request->name == null) {
            return response()->json(['error' => __('Conversation title name is missing.')], 412);
        }

        $chat = ChatConversation::where('conversation_id', request('conversation_id'))->first(); 

        if ($chat) {
            if ($chat->user_id == auth()->user()->id){

                $chat->title = request('name');
                $chat->save();

                return response()->json(['message' => __('Success')], 200); 
    
            } else{
                return response()->json(['error' => __('This conversation ID does not belog to your user')], 405);
            }
        } else {
            return response()->json(['error' => __('Conversation not found')], 404);
        }

        
	}


    /**
     * Convert message text to audio
     *
     * @OA\Post(
     *      path="/api/v1/chat/conversation/message/listen",
     *      operationId="listenMessage",
     *      tags={"AI Chat (Conversation)"},
     *      summary="Convert chat message to audio",
     *      description="Convert chat message to audio via OpenAI's Whisper feature",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="message_id",
     *          in="path",
     *          description="ID of the target message",
     *          required=true,
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="status", type="boolean", description="Status of the task"),
     *              @OA\Property(property="audio_url", type="string", description="Audio file url for the AI response text"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Message Not Found",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
	public function listen(Request $request) 
    {
        if($request->message_id == null) {
            return response()->json(['error' => __('Message ID is missing.')], 412);
        }

        $voice = config('settings.chat_default_voice');

        $message = ChatHistory::where('user_id', auth()->user()->id)->where('id', $request->message_id)->get();

        if (is_null($message)) {
            return response()->json(['error' => __('Message Not Found.')], 404);
        }

        # Count characters 
        $total_characters = mb_strlen($message->response, 'UTF-8');

        # Check if user has characters available to proceed
        if (auth()->user()->available_chars != -1) {
            if ((Auth::user()->available_chars + Auth::user()->available_chars_prepaid) < $total_characters) {
                return response()->json(['error' => __('Not sufficient characters to generate audio')], 405);
            } else {
                $this->updateAvailableCharacters($total_characters);
            } 
        }

        if (config('settings.personal_openai_api') == 'allow') {
            if (is_null(auth()->user()->personal_openai_key)) {
                return response()->json(['error' => __('You must include your personal Openai API key in your profile settings first')], 405);
            } else {
                config(['openai.api_key' => auth()->user()->personal_openai_key]); 
            } 

        } elseif (!is_null(auth()->user()->plan_id)) {
            $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($check_api->personal_openai_api) {
                if (is_null(auth()->user()->personal_openai_key)) {
                    return response()->json(['error' => __('You must include your personal Openai API key in your profile settings first')], 405);
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
                'input' => $message->response,
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


            return response()->json(['status' => true, 'audio_url' => $result_url], 200);

        } catch(Exception $e) {
            return response()->json(['error' => __('There was an error during audio file creation')], 419);
        }

	}


    /**
     * Delete Chat Converstion
     *
     * @OA\Delete(
     *      path="/api/v1/chat/conversation/delete",
     *      operationId="deleteConversation",
     *      tags={"AI Chat (Conversation)"},
     *      summary="Delete a Chat Conversation",
     *      description="Delete a chat conversation",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="conversation_id",
     *          in="path",
     *          description="ID of the target conversation",
     *          required=true,
     *          @OA\Schema(type="string", example="TKDRYWDASE"),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Chatbot Not Found",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
	public function delete(Request $request) 
    {
        if($request->conversation_id == null) {
            return response()->json(['error' => __('Conversation ID is missing.')], 412);
        }

        $chat = ChatConversation::where('conversation_id', request('conversation_id'))->first(); 

        if ($chat) {
            if ($chat->user_id == auth()->user()->id){

                $chat->delete();

                if (session()->has('conversation_id')) {
                    session()->forget('conversation_id');
                }

                return response()->json(['message' => __('Success')], 200);
    
            } else{
                return response()->json(['error' => __('This conversation ID does not belog to your user')], 405);
            }
        } else {
            return response()->json(['error' => __('Conversation not found')], 404);
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
