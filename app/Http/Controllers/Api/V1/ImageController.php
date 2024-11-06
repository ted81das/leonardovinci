<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Orhanerday\OpenAi\OpenAi;
use App\Models\SubscriptionPlan;
use App\Models\Image;
use App\Models\User;
use App\Models\ApiKey;

class ImageController extends Controller
{
    /**
     * Generaet AI Image
     *
     * @OA\Post(
     *      path="/api/v1/image/generate",
     *      operationId="imageGenerate",
     *      tags={"AI Image"},
     *      summary="AI Text to Image feature",
     *      description="Convert your text prompt to images with Dalle and Stable Diffusion",
     *      security={{ "passport": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="User data",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="vendor",
     *                      description="AI Image vendor. Supported values are: openai | stable_diffusion. Stable Diffusion supports only latest SD 3, SD 3 Turbo and SD Core models. OpenAI supports Dalle 2, Dalle 3, Dalle 3 HD models.",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                     property="prompt",
     *                     description="Input prompt text to generate AI Image",
     *                     type="string",
     *                 ),
     *                  @OA\Property(
     *                      property="resolution",
     *                      description="Image result of the target image. IMPORTANT: For OpenAI image resultion vary between Dalle 2 and Dalle 3 refer to OpenAI documentations on exact resolutions in pixels. For SD include supported aspect rations, refer to SD documentations for all options.",
     *                      type="string"
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Image generated successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="image_id", type="integer", description="ID of the image in the database table"),
     *              @OA\Property(property="image_url", type="string", description="URL of the generated image"),
     *              @OA\Property(property="status", type="boolean", description="Status of the task, true = success"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      ),     *      
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized: User not authenticated",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed, restrictions applied",
     *      ),
     * )
    */
	public function generate(Request $request) 
    {
        if (config('settings.personal_openai_api') == 'allow') {
            if (is_null(auth()->user()->personal_openai_key)) {
                return response()->json(['error' => __('You must include your personal Openai API key in your profile settings first')], 405);
            } else {
                $open_ai = new OpenAi(auth()->user()->personal_openai_key);
            } 

        } elseif (!is_null(auth()->user()->plan_id)) {
            $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($check_api->personal_openai_api) {
                if (is_null(auth()->user()->personal_openai_key)) {
                    return response()->json(['error' => __('You must include your personal Openai API key in your profile settings first')], 405); 
                } else {
                    $open_ai = new OpenAi(auth()->user()->personal_openai_key);
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

        $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
        $results = [];
        

        # Verify if user has enough credits
        if ($request->vendor == 'openai') {
            if (auth()->user()->available_dalle_images != -1) {
                if ((auth()->user()->available_dalle_images + auth()->user()->available_dalle_images_prepaid) < $request->max_results) {
                    if (!is_null(auth()->user()->member_of)) {
                        if (auth()->user()->member_use_credits_image) {
                            $member = User::where('id', auth()->user()->member_of)->first();
                            if (($member->available_dalle_images + $member->available_dalle_images_prepaid) < $request->max_results) {
                                return response()->json(['error' => __('Not enough Dalle image balance to proceed')], 405);
                            }
                        } else {
                            return response()->json(['error' => __('Not enough Dalle image balance to proceed')], 405);
                        }
                        
                    } else {
                        return response()->json(['error' => __('Not enough Dalle image balance to proceed')], 405);
                    } 
                }
            }
        } elseif ($request->vendor == 'stable_diffusion') {
            if (auth()->user()->available_sd_images != -1) {
                if ((auth()->user()->available_sd_images + auth()->user()->available_sd_images_prepaid) < $request->max_results) {
                    if (!is_null(auth()->user()->member_of)) {
                        if (auth()->user()->member_use_credits_image) {
                            $member = User::where('id', auth()->user()->member_of)->first();
                            if (($member->available_sd_images + $member->available_sd_images_prepaid) < $request->max_results) {
                                return response()->json(['error' => __('Not enough Stable Diffusion image balance to proceed')], 405);
                            }
                        } else {
                            return response()->json(['error' => __('Not enough Stable Diffusion image balance to proceed')], 405);
                        }
                        
                    } else {
                        return response()->json(['error' => __('Not enough Stable Diffusion image balance to proceed')], 405);
                    } 
                }
            }
        }
        


        $max_results = 1;
        $plan_type = (auth()->user()->plan_id) ? 'paid' : 'free';  

        $prompt = $request->prompt;

        if ($request->vendor == 'openai') {

            if ($plan) {
                if (is_null($plan->dalle_image_engine)) {
                    $model = 'dall-e-2';    
                } elseif ($plan->dalle_image_engine == 'none') {
                    return response()->json(['error' => __('Your subscription plan does not cover Dalle models')], 405);
                } else {
                    $model = $plan->dalle_image_engine;
                }
            } else {
                $model = config('settings.image_dalle_engine'); 
            }

            if ($model == 'dall-e-3-hd') {
                $complete = $open_ai->image([
                    'model' => 'dall-e-3',
                    'prompt' => $prompt,
                    'size' => $request->resolution,
                    'n' => $max_results,
                    "response_format" => "url",
                    'quality' => "hd",
                ]);
            } else {
                $complete = $open_ai->image([
                    'model' => $model,
                    'prompt' => $prompt,
                    'size' => $request->resolution,
                    'n' => $max_results,
                    "response_format" => "url",
                    'quality' => "standard",
                ]);
            } 
        

            $response = json_decode($complete , true);

            if (isset($response['data'])) {
                $url = $response['data'][0]['url'];

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_URL, $url);
                $contents = curl_exec($curl);
                curl_close($curl);


                $name = 'dalle-' . Str::random(10) . '.png';

                if (config('settings.default_storage') == 'local') {
                    Storage::disk('public')->put('images/' . $name, $contents);
                    $image_url = 'images/' . $name;
                    $storage = 'local';
                } elseif (config('settings.default_storage') == 'aws') {
                    Storage::disk('s3')->put('images/' . $name, $contents, 'public');
                    $image_url = Storage::disk('s3')->url('images/' . $name);
                    $storage = 'aws';
                } elseif (config('settings.default_storage') == 'r2') {
                    Storage::disk('r2')->put('images/' . $name, $contents, 'public');
                    $image_url = Storage::disk('r2')->url('images/' . $name);
                    $storage = 'r2';
                } elseif (config('settings.default_storage') == 'wasabi') {
                    Storage::disk('wasabi')->put('images/' . $name, $contents);
                    $image_url = Storage::disk('wasabi')->url('images/' . $name);
                    $storage = 'wasabi';
                } elseif (config('settings.default_storage') == 'gcp') {
                    Storage::disk('gcs')->put('images/' . $name, $contents);
                    Storage::disk('gcs')->setVisibility('images/' . $name, 'public');
                    $image_url = Storage::disk('gcs')->url('images/' . $name);
                    $storage = 'gcp';
                } elseif (config('settings.default_storage') == 'storj') {
                    Storage::disk('storj')->put('images/' . $name, $contents, 'public');
                    Storage::disk('storj')->setVisibility('images/' . $name, 'public');
                    $image_url = Storage::disk('storj')->temporaryUrl('images/' . $name, now()->addHours(167));
                    $storage = 'storj';                        
                } elseif (config('settings.default_storage') == 'dropbox') {
                    Storage::disk('dropbox')->put('images/' . $name, $contents);
                    $image_url = Storage::disk('dropbox')->url('images/' . $name);
                    $storage = 'dropbox';
                }

                $content = new Image();
                $content->user_id = auth()->user()->id;
                $content->description = $request->prompt;
                $content->resolution = $request->resolution;
                $content->image = $image_url;
                $content->plan_type = $plan_type;
                $content->storage = $storage;
                $content->image_name = 'images/' . $name;
                $content->vendor = 'dalle';
                $content->image_style = $request->style;
                $content->image_lighting = $request->lightning;
                $content->image_artist = $request->artist;
                $content->image_mood = $request->mood;
                $content->image_medium = $request->medium;
                $content->vendor_engine = $model;
                $content->save();

 
                # Update credit balance
                $this->updateBalance(1, $request->vendor);

                $image_url = ($storage == 'local') ? URL::asset($image_url) : $image_url;

                return response()->json(['status' => true, 'image_url' => $image_url, 'image_id' => $content->id], 200); 

            } else {
                if ($response['error']['code'] == 'invalid_api_key') {
                    return response()->json(['error' => __('Please try again, Dalle 3 model limit has been reached for today')], 405);
                } else {
                    $message = $response['error']['message'];
                    return response()->json(['error' =>  $message], 405);
                }
            }

        } elseif ($request->vendor == 'stable_diffusion') {

            if ($plan) {
                if (is_null($plan->sd_image_engine)) {
                    $sd_model = config('settings.image_stable_diffusion_engine');   
                } elseif ($plan->sd_image_engine == 'none') {
                    return response()->json(['error' => __('Your subscription plan does not cover Stable Diffusion models')], 405);
                } else {
                    $sd_model = $plan->sd_image_engine;
                }
            } else {
                $sd_model = config('settings.image_stable_diffusion_engine');
            }


            if (config('settings.personal_sd_api') == 'allow') {
                if (is_null(auth()->user()->personal_sd_key)) {
                    return response()->json(['error' => __('You must include your personal Stable Diffusion API key in your profile settings first')], 405); 
                } else {
                    $stable_diffusion = auth()->user()->personal_sd_key;
                } 
    
            } elseif (!is_null(auth()->user()->plan_id)) {
                $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                if ($check_api->personal_sd_api) {
                    if (is_null(auth()->user()->personal_sd_key)) {
                        return response()->json(['error' => __('You must include your personal Stable Diffusion API key in your profile settings first')], 405); 
                    } else {
                        $stable_diffusion = auth()->user()->personal_sd_key;
                    }
                } else {
                    if (config('settings.sd_key_usage') == 'main') {
                        $stable_diffusion = config('services.stable_diffusion.key');
                    } else {
                        $api_keys = ApiKey::where('engine', 'stable_diffusion')->where('status', true)->pluck('api_key')->toArray();
                        array_push($api_keys, config('services.stable_diffusion.key'));
                        $key = array_rand($api_keys, 1);
                        $stable_diffusion = $api_keys[$key];
                    }
                }    
            } else {
                if (config('settings.sd_key_usage') == 'main') {
                    $stable_diffusion = config('services.stable_diffusion.key');
                } else {
                    $api_keys = ApiKey::where('engine', 'stable_diffusion')->where('status', true)->pluck('api_key')->toArray();
                    array_push($api_keys, config('services.stable_diffusion.key'));
                    $key = array_rand($api_keys, 1);
                    $stable_diffusion = $api_keys[$key];
                }
            }


            $sd_mode = ($sd_model == 'sd3-turbo' || $sd_model == 'sd3') ? 'sd3' : 'core';

            $url = 'https://api.stability.ai/v2beta/stable-image/generate/' . $sd_mode;

            $headers = [
                'Authorization:' . $stable_diffusion,
                'Content-Type: multipart/form-data',
                'Accept: application/json',
            ];

            $postFields = array(
                'prompt' => $prompt,
                'model' => $sd_model,
                'aspect_ratio' => $request->resolution,
            );                 


            $ch = curl_init($url); 
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($result , true);

            if (isset($response['finish_reason'])) {
                if ($response['finish_reason'] == 'SUCCESS' || $response['finish_reason'] == 'CONTENT_FILTERED') {

                    $image = base64_decode($response['image']);

                    $name = 'sd-' . Str::random(10) . '.png';

                    if (config('settings.default_storage') == 'local') {
                        Storage::disk('public')->put('images/' . $name, $image);
                        $image_url = 'images/' . $name;
                        $storage = 'local';
                    } elseif (config('settings.default_storage') == 'aws') {
                        Storage::disk('s3')->put('images/' . $name, $image, 'public');
                        $image_url = Storage::disk('s3')->url('images/' . $name);
                        $storage = 'aws';
                    } elseif (config('settings.default_storage') == 'r2') {
                        Storage::disk('r2')->put('images/' . $name, $image, 'public');
                        $image_url = Storage::disk('r2')->url('images/' . $name);
                        $storage = 'r2';
                    } elseif (config('settings.default_storage') == 'wasabi') {
                        Storage::disk('wasabi')->put('images/' . $name, $image);
                        $image_url = Storage::disk('wasabi')->url('images/' . $name);
                        $storage = 'wasabi';
                    } elseif (config('settings.default_storage') == 'gcp') {
                        Storage::disk('gcs')->put('images/' . $name, $image);
                        Storage::disk('gcs')->setVisibility('images/' . $name, 'public');
                        $image_url = Storage::disk('gcs')->url('images/' . $name);
                        $storage = 'gcp';
                    } elseif (config('settings.default_storage') == 'storj') {
                        Storage::disk('storj')->put('images/' . $name, $image, 'public');
                        Storage::disk('storj')->setVisibility('images/' . $name, 'public');
                        $image_url = Storage::disk('storj')->temporaryUrl('images/' . $name, now()->addHours(167));
                        $storage = 'storj';                        
                    } elseif (config('settings.default_storage') == 'dropbox') {
                        Storage::disk('dropbox')->put('images/' . $name, $image);
                        $image_url = Storage::disk('dropbox')->url('images/' . $name);
                        $storage = 'dropbox';
                    }

                    $content = new Image();
                    $content->user_id = auth()->user()->id;
                    $content->description = $request->prompt;
                    $content->resolution = $request->resolution;
                    $content->image = $image_url;
                    $content->plan_type = $plan_type;
                    $content->storage = $storage;
                    $content->image_name = 'images/' . $name;
                    $content->vendor = 'sd';
                    $content->image_style = $request->style;
                    $content->image_lighting = $request->lightning;
                    $content->image_artist = $request->artist;
                    $content->image_mood = $request->mood;
                    $content->image_medium = $request->medium;
                    $content->negative_prompt = $request->negative_prompt;
                    $content->sd_clip_guidance = $request->preset;
                    $content->sd_prompt_strength = $request->cfg_scale;
                    $content->sd_diffusion_samples = $request->diffusion_samples;
                    $content->sd_steps = $request->steps;
                    $content->vendor_engine = $sd_model;
                    $content->save();

                    # Update credit balance
                    $this->updateBalance(1, $request->vendor);

                    $image_url = ($storage == 'local') ? URL::asset($image_url) : $image_url;

                    return response()->json(['status' => true, 'image_url' => $image_url, 'image_id' => $content->id], 200); 

                }

                
            } else {

                if (isset($response['name'])) {
                    if ($response['name'] == 'insufficient_balance') {
                        return response()->json(['error' => __('You do not have sufficent balance in your Stable Diffusion account to generate new images')], 405);
                    } elseif (($response['name'] == 'content_moderation')) {
                        return response()->json(['error' => __('Your request was flagged by SD content moderation system, as a result your request was denied')], 405);
                    } else {
                        return response()->json(['error' => __('There was an issue generating your AI Image, please try again or contact support team')], 405);
                    }
                } else {
                    return response()->json(['error' => __('There was an issue generating your AI Image, please try again or contact support team')], 405);
                }

            }

        }
           
	}


    /**
	*
	* Update user image balance
	* @param - total words generated
	* @return - confirmation
	*
	*/
    public function updateBalance($images, $vendor) {

        $user = User::find(Auth::user()->id);

        if ($vendor == 'openai') {
            if (auth()->user()->available_dalle_images != -1) {
        
                if (Auth::user()->available_dalle_images > $images) {
    
                    $total_dalle_images = Auth::user()->available_dalle_images - $images;
                    $user->available_dalle_images = ($total_dalle_images < 0) ? 0 : $total_dalle_images;
    
                } elseif (Auth::user()->available_dalle_images_prepaid > $images) {
    
                    $total_dalle_images_prepaid = Auth::user()->available_dalle_images_prepaid - $images;
                    $user->available_dalle_images_prepaid = ($total_dalle_images_prepaid < 0) ? 0 : $total_dalle_images_prepaid;
    
                } elseif ((Auth::user()->available_dalle_images + Auth::user()->available_dalle_images_prepaid) == $images) {
    
                    $user->available_dalle_images = 0;
                    $user->available_dalle_images_prepaid = 0;
    
                } else {
    
                    if (!is_null(Auth::user()->member_of)) {
    
                        $member = User::where('id', Auth::user()->member_of)->first();
    
                        if ($member->available_dalle_images > $images) {
    
                            $total_dalle_images = $member->available_dalle_images - $images;
                            $member->available_dalle_images = ($total_dalle_images < 0) ? 0 : $total_dalle_images;
                
                        } elseif ($member->available_dalle_images_prepaid > $images) {
                
                            $total_dalle_images_prepaid = $member->available_dalle_images_prepaid - $images;
                            $member->available_dalle_images_prepaid = ($total_dalle_images_prepaid < 0) ? 0 : $total_dalle_images_prepaid;
                
                        } elseif (($member->available_dalle_images + $member->available_dalle_images_prepaid) == $images) {
                
                            $member->available_dalle_images = 0;
                            $member->available_dalle_images_prepaid = 0;
                
                        } else {
                            $remaining = $images - $member->available_dalle_images;
                            $member->available_dalle_images = 0;
            
                            $prepaid_left = $member->available_dalle_images_prepaid - $remaining;
                            $member->available_dalle_images_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                        }
    
                        $member->update();
    
                    } else {
                        $remaining = $images - Auth::user()->available_dalle_images;
                        $user->available_dalle_images = 0;
    
                        $prepaid_left = Auth::user()->available_dalle_images_prepaid - $remaining;
                        $user->available_dalle_images_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                    }
                }
            }
    
            $user->update();
        
        } else {
            if (auth()->user()->available_sd_images != -1) {
        
                if (Auth::user()->available_sd_images > $images) {
    
                    $total_sd_images = Auth::user()->available_sd_images - $images;
                    $user->available_sd_images = ($total_sd_images < 0) ? 0 : $total_sd_images;
    
                } elseif (Auth::user()->available_sd_images_prepaid > $images) {
    
                    $total_sd_images_prepaid = Auth::user()->available_sd_images_prepaid - $images;
                    $user->available_sd_images_prepaid = ($total_sd_images_prepaid < 0) ? 0 : $total_sd_images_prepaid;
    
                } elseif ((Auth::user()->available_sd_images + Auth::user()->available_sd_images_prepaid) == $images) {
    
                    $user->available_sd_images = 0;
                    $user->available_sd_images_prepaid = 0;
    
                } else {
    
                    if (!is_null(Auth::user()->member_of)) {
    
                        $member = User::where('id', Auth::user()->member_of)->first();
    
                        if ($member->available_images > $images) {
    
                            $total_sd_images = $member->available_sd_images - $images;
                            $member->available_sd_images = ($total_sd_images < 0) ? 0 : $total_sd_images;
                
                        } elseif ($member->available_sd_images_prepaid > $images) {
                
                            $total_sd_images_prepaid = $member->available_sd_images_prepaid - $images;
                            $member->available_sd_images_prepaid = ($total_sd_images_prepaid < 0) ? 0 : $total_sd_images_prepaid;
                
                        } elseif (($member->available_sd_images + $member->available_sd_images_prepaid) == $images) {
                
                            $member->available_sd_images = 0;
                            $member->available_sd_images_prepaid = 0;
                
                        } else {
                            $remaining = $images - $member->available_sd_images;
                            $member->available_sd_images = 0;
            
                            $prepaid_left = $member->available_sd_images_prepaid - $remaining;
                            $member->available_sd_images_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                        }
    
                        $member->update();
    
                    } else {
                        $remaining = $images - Auth::user()->available_sd_images;
                        $user->available_sd_images = 0;
    
                        $prepaid_left = Auth::user()->available_sd_images_prepaid - $remaining;
                        $user->available_sd_images_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                    }
                }
            }
    
            $user->update();
        }

        

    }


    /**
     * Delete image
     *
     * Delete the target image based on image id.
     *
     * @OA\Delete(
     *      path="/api/v1/image/delete",
     *      operationId="imageDelete",
     *      tags={"AI Image"},
     *      summary="Delete AI Image result",
     *      description="Delete AI Text to Image result based on provided image ID.",
     *      security={{ "passport": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="User data",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="image_id",
     *                      description="ID of the target image to delete.",
     *                      type="integer"
     *                  ),
     *              ),
     *          ),
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
     *          response=404,
     *          description="Image not found",
     *      ),
     * )
    */
	public function delete(Request $request) 
    {
        $image = Image::where('id', $request->image_id)->first(); 

        if ($image) {
            if ($image->user_id == auth()->user()->id){

                switch ($image->storage) {
                    case 'local':
                        if (Storage::disk('public')->exists($image->image)) {
                            Storage::disk('public')->delete($image->image);
                        }
                        break;
                    case 'aws':
                        if (Storage::disk('s3')->exists($image->image_name)) {
                            Storage::disk('s3')->delete($image->image_name);
                        }
                        break;
                    case 'r2':
                        if (Storage::disk('r2')->exists($image->image_name)) {
                            Storage::disk('r2')->delete($image->image_name);
                        }
                        break;
                    case 'wasabi':
                        if (Storage::disk('wasabi')->exists($image->image_name)) {
                            Storage::disk('wasabi')->delete($image->image_name);
                        }
                        break;
                    case 'storj':
                        if (Storage::disk('storj')->exists($image->image_name)) {
                            Storage::disk('storj')->delete($image->image_name);
                        }
                        break;
                    case 'gcp':
                        if (Storage::disk('gcs')->exists($image->image_name)) {
                            Storage::disk('gcs')->delete($image->image_name);
                        }
                        break;
                    case 'dropbox':
                        if (Storage::disk('dropbox')->exists($image->image_name)) {
                            Storage::disk('dropbox')->delete($image->image_name);
                        }
                        break;
                    default:
                        # code...
                        break;
                }
    
                $image->delete();
    
                return response()->json(['message' => __('Image has been successfully deleted')], 201);
    
            } else{
    
                return response()->json(['error' => __('Unauthorized task.')], 405);
            }  
        } else {
            return response()->json(['error' => __('Image not found.')], 404);
        }
	}



    public function build_post_fields( $data,$existingKeys='',&$returnArray=[])
    {
        if(($data instanceof \CURLFile) or !(is_array($data) or is_object($data))){
            $returnArray[$existingKeys]=$data;
            return $returnArray;
        }
        else{
            foreach ($data as $key => $item) {
                $this->build_post_fields($item,$existingKeys?$existingKeys."[$key]":$key,$returnArray);
            }
            return $returnArray;
        }
    }
    


}
