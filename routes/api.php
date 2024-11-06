<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers\Api\V1'], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/register', 'register');
        Route::post('/logout', 'logout');
        Route::post('/forgot-password', 'sendPasswordResetMail');  
        Route::post('email/verify/resend', 'requestEmailVerificationCode')->middleware('throttle:6,1'); 
        Route::post('email/verify',  'verifyEmail');
    }); 
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function () {
    Route::group(['prefix' => 'auth', 'namespace' => 'App\Http\Controllers\Api\V1'], function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('/logout', 'logout');      
        }); 
    });
});

Route::middleware('auth:api')->group(function () {
    Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\Api\V1'], function() {

        # USER PROFILE API ROUTES
        Route::controller(UserController::class)->group(function () {
            Route::get('/user/profile', 'index');
            Route::get('/user/profile/statistics', 'statistics');
            Route::put('/user/profile', 'update');
            Route::post('/user/profile/api', 'storeAPI');  
            Route::post('/user/profile/referral', 'updateReferral');   
            Route::delete('/user/profile', 'delete');        
        }); 

        # ADMIN USER MANAGEMENT API ROUTES
        Route::controller(UserManagementController::class)->group(function () {
            Route::get('/admin/users/list', 'listUsers');
            Route::get('/admin/users/{user_id}', 'showUser');
            Route::post('/admin/user/create', 'createUser'); 
            Route::delete('/admin/user/delete', 'deleteUser'); 
            Route::post('/admin/user/increase-balance', 'increaseBalance');       
            Route::post('/admin/user/assign-subscription', 'assignSubscription');       
        });

        # AI CHAT (GENERAL) API ROUTES
        Route::controller(ChatGeneralController::class)->group(function () {        
            Route::get('/chats', 'index');   
            Route::post('/chats/favorite', 'favorite');  
            Route::get('/chats/view/original/{chat_code}', 'view');
            Route::get('/chats/view/custom/{chat_code}', 'viewCustom'); 
        });

        # AI CHAT (CONVERSATION) API ROUTES
        Route::controller(ChatConversationController::class)->group(function () {  
            Route::post('/chat/conversation', 'conversation'); 
            Route::get('/chat/conversation/{conversation_id}/messages', 'messages'); 
            Route::get('/chat/conversation/{conversation_id}/messages/{message_id}', 'message'); 
            Route::post('/chat/conversation/rename', 'rename');
            Route::post('/chat/conversation/listen', 'listen');
            Route::post('/chat/conversation/delete', 'delete');
            Route::post('/chat/conversation/process', 'process');   
            Route::post('/chat/process/custom', 'processCustom');       
        });

        # AI WRITER API ROUTES
        Route::controller(WriterController::class)->group(function () {
            Route::get('/templates', 'index');       
            Route::post('/template/generate', 'generate');        
            Route::get('/template/{template_code}', 'viewTemplate');              
            Route::post('/template/save', 'save');          
            Route::post('/templates/favorite', 'favorite');         
            
        });

        # AI IMAGE API ROUTES
        Route::controller(ImageController::class)->group(function () {  
            Route::post('/image/generate', 'generate');                
            Route::delete('/image/delete', 'delete');
        });

        # SPEECH TO TEXT API ROUTES
        Route::controller(TranscribeController::class)->group(function () {    
            Route::post('/speech/transcribe', 'transcribe');                
        });

        # AFFILIATE PROGRAM API ROUTES
        Route::controller(ReferralController::class)->group(function() {
            Route::get('/referrals', 'referrals');
            Route::post('/referrals/payouts/request', 'payoutRequest');
            Route::post('/referrals/settings', 'settings');
        });

        # SUPPORT TICKETS API ROUTES
        Route::controller(SupportController::class)->group(function () {
            Route::get('/support', 'supportTickets');      
            Route::post('/support', 'createTicket');         
            Route::get('/support/ticket/{ticket_id}', 'supportMessages');         
            Route::post('/support/send-message', 'sendMessage');         
            Route::delete('/support/ticket/{ticket_id}', 'delete');
        });

        

    });
});
