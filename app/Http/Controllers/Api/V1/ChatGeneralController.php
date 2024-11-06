<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Statistics\UserService;
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


class ChatGeneralController extends Controller
{

     /**
     * Get list of chats
     *
     * @OA\Get(
     *      path="/api/v1/chats",
     *      operationId="chatList",
     *      tags={"AI Chat (General)"},
     *      summary="Get list of original and custom chatbots",
     *      description="Get list of original and custom chatbots (both created by the admin and the requesting user).",
     *      security={{ "passport": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="categories", type="object", description="Eloguent object for list of chatbot categories"),
     *              @OA\Property(property="original_chatbots", type="object", description="Eloguent object for list of original chatbots that come with the script"),
     *              @OA\Property(property="custom_chatbots", type="object", description="Eloguent object for list of custom chatbots created by the user"),
     *              @OA\Property(property="public_custom_chatbots", type="object", description="Eloguent object for list of public custom chatbots created by admin"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     * )
    */
    public function index(Request $request)
    {      
        $categories = ChatCategory::orderBy('name', 'asc')->get(); 
        $original_chatbots = Chat::where('status', true)->orderBy('category', 'asc')->get();   
        $custom_chatbots = CustomChat::where('user_id', auth()->user()->id)->where('type', 'private')->where('status', true)->orderBy('group', 'asc')->get();  
        $public_custom_chatbots = CustomChat::where('type', 'custom')->where('status', true)->orderBy('group', 'asc')->get();  
        
        return response()->json(['categories' => $categories, 'original_chatbots' => $original_chatbots, 'custom_chatbots' => $custom_chatbots, 'public_custom_chatbots' => $public_custom_chatbots], 200); 
    }


   /**
     * View original chat
     *
     * @OA\Get(
     *      path="/api/v1/chat/view/original/{chat_code}",
     *      operationId="chatOriginal",
     *      tags={"AI Chat (General)"},
     *      summary="View original chatbot",
     *      description="Call original chatbot based on it chat_code",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="chat_code",
     *          in="path",
     *          description="Chat code that you want to view",
     *          required=true,
     *          @OA\Schema(type="string", example="TKDRY"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="chat", type="object", description="Eloguent object for target chatbot"),
     *              @OA\Property(property="conversations", type="object", description="Eloguent object for list of all conversations recorded for this chatbot under this user ID"),
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
	public function view(Request $request, String $chat_code) 
    {
        if (session()->has('conversation_id')) {
            session()->forget('conversation_id');
        }

        if($chat_code == null) {
            return response()->json(['error' => __('Chat code is missing.')], 412);
        }

        $chat = Chat::where('chat_code', $chat_code)->first(); 
        if ($chat) {
            $conversations = ChatConversation::where('user_id', auth()->user()->id)->where('chat_code', $chat->chat_code)->orderBy('updated_at', 'desc')->get(); 
            return response()->json(['chat' => $chat, 'conversation' => $conversations], 200); 
        } else {
            return response()->json(['error' => __('Chatbot Not Found.')], 404);
        }
        
	}


    /**
     * View custom chat
     *
     * @OA\Get(
     *      path="/api/v1/chat/view/custom/{chat_code}",
     *      operationId="chatCustom",
     *      tags={"AI Chat (General)"},
     *      summary="View custom chatbot",
     *      description="Call custom chatbot based on it chat_code",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="chat_code",
     *          in="path",
     *          description="Chat code that you want to view",
     *          required=true,
     *          @OA\Schema(type="string", example="TKDRY"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="chat", type="object", description="Eloguent object for target chatbot"),
     *              @OA\Property(property="conversations", type="object", description="Eloguent object for list of all conversations recorded for this chatbot under this user ID"),
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
    public function viewCustom(Request $request, String $chat_code) 
    {
        if (session()->has('conversation_id')) {
            session()->forget('conversation_id');
        }

        if($chat_code == null) {
            return response()->json(['error' => __('Chat code is missing.')], 412);
        }

        $chat = CustomChat::where('chat_code', $chat_code)->first(); 
        if ($chat) {
            $conversations = ChatConversation::where('user_id', auth()->user()->id)->where('chat_code', $chat->chat_code)->orderBy('updated_at', 'desc')->get(); 
            return response()->json(['chat' => $chat, 'conversation' => $conversations], 200); 
        } else {
            return response()->json(['error' => __('Chatbot Not Found.')], 404);
        }
	}



    /**
     * Set AI Chat to be favorite
     *
     * @OA\Post(
     *      path="/api/v1/chat/favorite",
     *      operationId="chatFavorite",
     *      tags={"AI Chat (General)"},
     *      summary="Set AI Chat as favorite",
     *      description="Set AI Chat to be favorite.",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="chat_code",
     *          in="path",
     *          description="Chat code that you want to set as favorite",
     *          required=true,
     *          @OA\Schema(type="string", example="TKDRY"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     * )
    */
	public function favorite(Request $request) 
    {
        if($request->chat_code == null) {
            return response()->json(['error' => __('Chat code is missing.')], 412);
        }

        if (strlen(request('chat_code')) < 6) {
            $chat = Chat::where('chat_code', request('chat_code'))->first(); 
        } else {
            $chat = CustomChat::where('chat_code', request('chat_code'))->first();
        }

        $favorite = FavoriteChat::where('chat_code', $chat->chat_code)->where('user_id', auth()->user()->id)->first();

        if ($favorite) {

            $favorite->delete();

            return response()->json(['message' => 'AI Chat has been removed from favorite list'], 201); 

        } else{

            $new_favorite = new FavoriteChat();
            $new_favorite->user_id = auth()->user()->id;
            $new_favorite->chat_code = $chat->chat_code;
            $new_favorite->save();

            return response()->json(['message' => 'AI Chat has been set as favorite'], 201); 
        }  
        
	}

}
