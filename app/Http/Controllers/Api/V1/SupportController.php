<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\SupportTicket;
use App\Models\SupportMessage;


class SupportController extends Controller
{
    /**
     * Gets all support requests
     *
     * @OA\Get(
     *      path="/api/v1/support/",
     *      operationId="supportTickets",
     *      tags={"Support Tickets"},
     *      summary="Gets all support tickets",
     *      description="Provide support tickets based on user role, admin has access to all tickets while individual users will get only tikcets created by them",
     *      security={{ "passport": {} }},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     * )
    */
    public function supportTickets(Request $request)
    {
        $perPage = $request->input('per_page', 10);

        $user = Auth::user();
        if ($user->group == 'admin')
            # Check for open tickets only via API
            $tickets = SupportTicket::orderBy('updated_at', 'asc')->paginate($perPage);
        else
            $tickets = SupportTicket::where([['user_id', $user->id]])->orderBy('updated_at', 'desc')->paginate($perPage);

        return response()->json($tickets, 200);
    }


    /**
     * Gets all messages of a support request
     *
     * @OA\Get(
     *      path="/api/v1/support/ticket/{ticket_id}",
     *      operationId="ticket",
     *      tags={"Support Tickets"},
     *      summary="Gets all messages of a support ticket",
     *      description="Gets all messages of a support ticket. Use ticket ids like TKD1234KZ not integers.",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="ticket_id",
     *          in="path",
     *          description="Ticket ID",
     *          required=true,
     *          @OA\Schema(type="string", example="TKD1234KZ"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Ticket Not Found",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
    public function supportMessages(Request $request, String $ticket_id)
    {   
        if($ticket_id == null) {
            return response()->json(['error' => __('Ticket ID missing.')], 412);
        } 

        $ticket = SupportTicket::where('ticket_id', $ticket_id)->first();

        if ($ticket) {
            if(Auth::user()->group != 'admin' && $ticket->user_id != Auth::id()){
                return response()->json(['error' => __('Unauthorized request.')], 403);
            }
    
            $perPage = $request->input('per_page', 10);
    
            $messages = SupportMessage::where([["ticket_id", $ticket->id]])->orderBy('updated_at', 'desc')->paginate($perPage);

            if ($messages) {
                return response()->json($messages, 200); 
            } else {
                return response()->json(['error' => __('Messages Not Found.')], 404);
            }
    
            
        } else {
            return response()->json(['error' => __('Ticket Not Found.')], 404);
        }

            
    }


    /**
     * Create new support ticket
     *
     * @OA\Post(
     *      path="/api/v1/support/create-ticket",
     *      operationId="createTicket",
     *      tags={"Support Tickets"},
     *      summary="Create new support ticket",
     *      description="Create new support ticket",
     *      security={{ "passport": {} }},
     *      @OA\RequestBody(
     *         required=true,
     *         description="Request support ticket data",
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="priority",
     *                     description="Ticket Priority. Supported values: Low | Normal | High | Critical",
     *                     type="string",
     *                     enum={"Low", "Normal", "High", "Critical"},
     *                 ),
     *                 @OA\Property(
     *                     property="category",
     *                     description="Ticket Category. Supported values: General Inquiry | Technical Issue | Billing Issue | Improvement Idea | Feedback | Other",
     *                     type="string",
     *                     enum={"General Inquiry", "Technical Issue", "Billing Issue", "Improvement Idea", "Feedback", "Other"}
     *                 ),
     *                  @OA\Property(
     *                     property="subject",
     *                     description="Subject of the Ticket",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     description="Support Ticket Message",
     *                     type="string"
     *                 ),
     *        
     *              
     *             ),
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
     * )
    */
    public function createTicket(Request $request)
    {   
        if($request->priority == null) return response()->json(['error' => __('Priority missing.')], 412);
        if($request->category == null) return response()->json(['error' => __('Category missing.')], 412);
        if($request->subject == null) return response()->json(['error' => __('Subject missing.')], 412);
        if($request->message == null) return response()->json(['error' => __('Message missing.')], 412);

        $ticket_id = strtoupper(Str::random(10));

        $ticket = new SupportTicket([
            'subject' => htmlspecialchars(request('subject')),
            'priority' => htmlspecialchars(request('priority')),
            'category' => htmlspecialchars(request('category')),
            'status' => 'Open',
            'user_id' => Auth::id(),
            'ticket_id' => $ticket_id,
        ]); 

        $ticket->save();

        $message = new SupportMessage([
            'message' => htmlspecialchars(request('message')),
            'user_id' => Auth::id(),
            'role' => Auth::user()->group,
            'ticket_id' => $ticket_id,
        ]); 
               
        $message->save();

        return response()->json(['message' => __('Ticket submitted')], 201);
        
    }

   /** 
    * Send message to support ticket
    *
    * @OA\Post(
    *      path="/api/v1/support/send-message",
    *      operationId="sendMessage",
    *      tags={"Support Tickets"},
    *      summary="Send message to the support ticket",
    *      description="Send message to the support ticket based on the ticket id",
    *      security={{ "passport": {} }},
    *      @OA\RequestBody(
    *         required=true,
    *         description="Request message data",
    *         @OA\MediaType(
    *             mediaType="multipart/form-data",
    *             @OA\Schema(
    *                 type="object",
    *                 @OA\Property(
    *                     property="ticket_id",
    *                     description="Ticket ID",
    *                     type="string",
    *                     example="TKD1234KZ"
    *                 ),
    *                 @OA\Property(
    *                     property="message",
    *                     description="Message to send",
    *                     type="string"
    *                 ),
    *             ),
    *         ),
    *      ),
    *      @OA\Response(
    *          response=201,
    *          description="Successful operation",
    *          @OA\JsonContent(
    *              type="object",
    *          ),
    *      ),
    *      @OA\Response(
    *          response=401,
    *          description="Unauthenticated",
    *      ),
    *      @OA\Response(
    *          response=412,
    *          description="Precondition Failed",
    *      ),
    *       @OA\Response(
    *          response=404,
    *          description="Ticket Not Found",
    *      ),
    * )
   */
   public function sendMessage(Request $request) {

       if($request->ticket_id == null) return response()->json(['error' => __('Ticket ID missing.')], 412);
       if($request->message == null) return response()->json(['error' => __('Message missing.')], 412);

       $ticket = SupportTicket::where('ticket_id', request('ticket_id'))->first();
       if ($ticket) {
            $ticket->updated_at = now();
            $ticket->save();
       } else {
            return response()->json(['error' => __('Ticket Not Found.')], 404);
       }
       

       $message = new SupportMessage([
           'message' => htmlspecialchars(request('message')),
           'user_id' => Auth::user()->id,
           'role' => Auth::user()->group,
           'ticket_id' => request('ticket_id'),
       ]); 
       
       $message->save();

       return response()->json(['message' => 'Message sent'], 201);

   }

    
    /** 
     * Delete support ticket
     *
     * @OA\Delete(
     *      path="/api/v1/support/ticket/{ticket}",
     *      operationId="deleteTicket",
     *      tags={"Support Tickets"},
     *      summary="Delete support ticket",
     *      description="Delete support ticket based on the provided ticket id.",
     *      security={{ "passport": {} }},
     *      @OA\Parameter(
     *          name="ticket_id",
     *          in="path",
     *          description="Ticket ID",
     *          required=true,
     *          @OA\Schema(type="string", example="TKD1234KZ"),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="object",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Unauthorized",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Ticket Not Found",
     *      ),
     *      @OA\Response(
     *          response=412,
     *          description="Precondition Failed",
     *      ),
    * )
   */
    public function delete(String $ticket_id)
    {   
        if($ticket_id == null) {
            return response()->json(['error' => __('Ticket ID missing.')], 412);
        } 

        $ticket = SupportTicket::where('ticket_id', $ticket_id)->first();

        if ($ticket) {
            if(Auth::user()->group != 'admin' && $ticket->user_id != Auth::id()){
                return response()->json(['error' => __('Unauthorized request.')], 403);
            } else {
                $ticket->delete();
                return response()->json(['message' => __('Ticket deleted')], 201);
            }
        } else {
            return response()->json(['message' => __('Ticket Not Found')], 404);
        }

        
    }

}
