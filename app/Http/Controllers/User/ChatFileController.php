<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ServerEvent;
use App\Models\ChatSpecial;
use App\Models\User;
use App\Models\ChatPrompt;
use App\Models\ChatHistorySpecial;
use App\Models\SubscriptionPlan;
use App\Services\QueryEmbedding;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\BrandVoice;
use App\Models\FineTuneModel;
use App\Services\HelperService;

class ChatFileController extends Controller
{

    protected QueryEmbedding $query;

    public function __construct(QueryEmbedding $query)
    {
        $this->query = $query;
    }

    public function index()
    {
        $chats = ChatSpecial::where('user_id', auth()->user()->id)->where('type', '<>', 'web')->orderBy('created_at', 'desc')->get();
        $first_chat = ChatSpecial::where('user_id', auth()->user()->id)->where('type', '<>', 'web')->first();
        $chat_code = ($first_chat) ? $first_chat->id : 'new';
        $prompts = ChatPrompt::where('status', true)->get();

        # Apply proper model based on role and subsciption
        if (auth()->user()->group == 'user') {
            $models = explode(',', config('settings.free_tier_models'));
        } elseif (!is_null(auth()->user()->plan_id)) {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            $models = explode(',', $plan->model_chat);
        } else {            
            $models = explode(',', config('settings.free_tier_models'));
        }

        $fine_tunes = FineTuneModel::all();
        $brands = BrandVoice::where('user_id', auth()->user()->id)->get();
        $brands_feature = \App\Services\HelperService::checkBrandsFeature();
        $default_model = auth()->user()->default_model_chat;

        if (auth()->user()->group == 'user') {
            if (config('settings.chat_file_user_access') != 'allow') {
                toastr()->warning(__('AI Chat File feature is not available for free tier users, subscribe to get a proper access'));
                return redirect()->route('user.plans');
            } else {
                $pdf_limit = config('settings.chat_pdf_file_size_user');
                $csv_limit = config('settings.chat_csv_file_size_user');
                $word_limit = config('settings.chat_word_file_size_user');
                return view('user.chat_file.index', compact('chats', 'chat_code', 'prompts', 'pdf_limit', 'csv_limit', 'word_limit', 'brands', 'models', 'fine_tunes', 'brands_feature', 'default_model'));
            }
        } elseif (auth()->user()->group == 'subscriber') {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($plan->file_chat_feature == false) {     
                toastr()->warning(__('Your current subscription plan does not include support for AI Chat File feature'));
                return redirect()->back();                   
            } else {
                $pdf_limit = config('settings.chat_pdf_file_size_user');
                $csv_limit = config('settings.chat_csv_file_size_user');
                $word_limit = config('settings.chat_word_file_size_user');
                return view('user.chat_file.index', compact('chats', 'chat_code', 'prompts', 'pdf_limit', 'csv_limit', 'word_limit', 'brands', 'models', 'fine_tunes', 'brands_feature', 'default_model'));
            }
        } else {
            $pdf_limit = config('settings.chat_pdf_file_size_user');
            $csv_limit = config('settings.chat_csv_file_size_user');
            $word_limit = config('settings.chat_word_file_size_user');
            return view('user.chat_file.index', compact('chats', 'chat_code', 'prompts', 'pdf_limit', 'csv_limit', 'word_limit', 'brands', 'models', 'fine_tunes', 'brands_feature', 'default_model'));
        }
   
    }

    public function conversation(Request $request)
    {
        if ($request->ajax()) {

            $messages = ChatHistorySpecial::where('user_id', auth()->user()->id)->where('chat_special_id', $request->chat_id)->get();

            $data['messages'] = $messages;

            return $data;
        }  
    }

    public function process(Request $request)
    {
        return response()->stream(function () use ($request) {
            try {
                $chat = ChatSpecial::where('user_id', auth()->user()->id)->where('id', $request->chat_id)->first();
                $question = $request->message;
                $queryVectors = $this->query->getQueryEmbedding($question);
                $vector = json_encode($queryVectors);
                $result = DB::table('embeddings')
                    ->select("text")
                    ->selectSub("embedding <=> '{$vector}'", "distance")
                    ->where('embedding_collection_id', $chat->embedding_collection->id)
                    ->orderBy('distance', 'asc')
                    ->limit(3)
                    ->get();
                $context = collect($result)->map(function ($item) {
                    return $item->text;
                })->implode("\n");

                $stream = $this->query->askQuestionStreamed($context, $question, $request->model);
                $resultText = "";
                foreach ($stream as $response) {
                    $text = $response->choices[0]->delta->content;
                    $resultText .= $text;
                    if (connection_aborted()) {
                        break;
                    }
                    ServerEvent::send($text, "");
                }

                $words = count(explode(' ', ($resultText)));                

                ChatHistorySpecial::insert([[
                    'chat_special_id' => $request->chat_id,
                    'role' => ChatHistorySpecial::ROLE_USER,
                    'content' => $question, 
                    'user_id' => auth()->user()->id,
                    'model' => $request->model,
                    'words' => 0,
                ], [
                    'chat_special_id' => $request->chat_id,
                    'role' => ChatHistorySpecial::ROLE_BOT,
                    'content' => $resultText,
                    'user_id' => auth()->user()->id,
                    'model' => $request->model,
                    'words' => $words,
                ]]);

                $chat->messages = $chat->messages + 1;
                $chat->save();                
                HelperService::updateBalance($words, $request->model);

            } catch (Exception $e) {
                Log::error($e);
                ServerEvent::send("");
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
        ]);
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

            $chat = ChatSpecial::where('id', request('chat_id'))->first(); 

            if ($chat) {
                if ($chat->user_id == auth()->user()->id){

                    $chat->title = request('name');
                    $chat->save();
    
                    $data['status'] = 'success';
                    $data['chat_id'] = request('chat_id');
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
	* Delete chat
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function delete(Request $request) 
    {
        if ($request->ajax()) {

            $chat = ChatSpecial::where('id', request('chat_id'))->first(); 

            if ($chat) {
                if ($chat->user_id == auth()->user()->id){

                    $chat->delete();
    
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


    public function checkBalance(Request $request)
    {
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

        $verify = HelperService::creditCheck($request->model, 20);
        if (isset($verify['status'])) {
            if ($verify['status'] == 'error') {
                return $verify;
            } else {
                $data['status'] = 'success';
                return $data;
            }
        } else {
            $data['status'] = 'success';
            return $data;
        }      
    }


    public function metainfo(Request $request)
    {
        if ($request->ajax()) {
            $chat = ChatSpecial::where('id', $request->chat_id)->first();

            if ($chat) {
                $data['status'] = 'success';
                $data['id'] = $chat->id;
                $data['title'] = $chat->title;
                $data['url'] = $chat->url;

                return $data;
            } else {
                $data['status'] = 'error';

                return $data;
            }
        }
        
    }


    public function credits(Request $request) 
    {
        if ($request->ajax()) {

            if (auth()->user()->available_words == -1) {
                $data['credits'] = 'Unlimited';
                return $data;
            } else {
                $data['credits'] = Auth::user()->available_words + Auth::user()->available_words_prepaid;
                return $data;
            }              
        }
	}

}
