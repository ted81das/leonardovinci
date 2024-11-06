<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *      version="1.0",
 *      title="DaVinci AI API Documentation",
 *      description="Full comprehensive RestAPI to develop anything you prefer for DaVinci AI",
 *      @OA\Contact(
 *          email="berkinedesign@gmail.com"
 *      ),
 * ),
 * 
 * @OA\Tag(
 *   name="Authentication",
 *   description="API Endpoints of Authentication methods"
 * ),
 * @OA\Tag(
 *   name="User Profile",
 *   description="API Endpoints of User Profile"
 * ),
 * @OA\Tag(
 *   name="User Management",
 *   description="API Endpoints for Admin User Management Features"
 * ),
 * @OA\Tag(
 *   name="AI Chat (General)",
 *   description="API Endpoints of listing all chats and viewing invidually"
 * ),
 * @OA\Tag(
 *   name="AI Chat (Conversation)",
 *   description="API Endpoints for chatbot conversation and related methods"
 * ),
 * @OA\Tag(
 *   name="AI Writer",
 *   description="API Endpoints of AI Writer feature to use templates"
 * ),
 * @OA\Tag(
 *   name="AI Image",
 *   description="API Endpoints of Text to Image Feature"
 * ),
 * @OA\Tag(
 *   name="AI Speech to Text",
 *   description="API Endpoints of Speech to Text Feature"
 * ),
 * @OA\Tag(
 *   name="Affiliate Program",
 *   description="API Endpoints of Affiliate Program Feature"
 * ),
 * @OA\Tag(
 *   name="Support Tickets",
 *   description="API Endpoints of Support Tickets Feature"
 * ),
 *
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}

