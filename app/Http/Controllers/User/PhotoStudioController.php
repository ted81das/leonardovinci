<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use App\Services\Statistics\UserService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\ApiKey;
use App\Models\Setting;
use App\Models\Image;
use App\Models\SdCost;
use GuzzleHttp\Exception\Report;

class PhotoStudioController extends Controller
{
    private $api;
    private $user;

    public function __construct()
    {
        $this->api = new LicenseController();
        $this->user = new UserService();
    }

    /** 
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   

        $verify = $this->api->verify_license();
        $type = (isset($verify['type'])) ? $verify['type'] : '';

        $studio = SdCost::first();
        
        if (auth()->user()->group == 'user') {
            if (config('settings.photo_studio_user_access') != 'allow') {
                toastr()->warning(__('AI Photo Studio feature is not available for free tier users, subscribe to get a proper access'));
                return redirect()->route('user.plans');
            } else {
                return view('user.photo_studio.index', compact('type', 'studio'));
            }
        } elseif (auth()->user()->group == 'subscriber') {
            $plan = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
            if ($plan->photo_studio_feature == false) {     
                toastr()->warning(__('Your current subscription plan does not include support for AI Photo Studio feature'));
                return redirect()->back();                   
            } else {
                return view('user.photo_studio.index', compact('type', 'studio'));
            }
        } else {
            return view('user.photo_studio.index', compact('type', 'studio'));
        }

    }


    /**
	*
	* Process Davinci Image to Video
	* @param - file id in DB
	* @return - confirmation
	*
	*/
	public function generate(Request $request) 
    {
        if ($request->ajax()) {

            if (config('settings.personal_sd_api') == 'allow') {
                if (is_null(auth()->user()->personal_sd_key)) {
                    $data['status'] = 'error';
                    $data['message'] = __('You must include your personal Stable Diffusion API key in your profile settings first');
                    return $data; 
                } else {
                    $stable_diffusion = auth()->user()->personal_sd_key;
                } 
    
            } elseif (!is_null(auth()->user()->plan_id)) {
                $check_api = SubscriptionPlan::where('id', auth()->user()->plan_id)->first();
                if ($check_api->personal_sd_api) {
                    if (is_null(auth()->user()->personal_sd_key)) {
                        $data['status'] = 'error';
                        $data['message'] = __('You must include your personal Stable Diffusion API key in your profile settings first');
                        return $data; 
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

            $settings = Setting::where('name', 'license')->first(); 
            $verify = $this->user->verify_license();
            if($settings->value != $verify['code']){return;}

            # Verify if user has enough credits
            if (auth()->user()->available_sd_images != -1) {
                if ((auth()->user()->available_sd_images + auth()->user()->available_sd_images_prepaid) < 1) {
                    if (!is_null(auth()->user()->member_of)) {
                        if (auth()->user()->member_use_credits_image) {
                            $member = User::where('id', auth()->user()->member_of)->first();
                            if (($member->available_sd_images + $member->available_sd_images_prepaid) < 1) {
                                $data['status'] = 'error';
                                $data['message'] = __('Not enough Stable Diffusion image balance to proceed, subscribe or top up your image balance and try again');
                                return $data;
                            }
                        } else {
                            $data['status'] = 'error';
                            $data['message'] = __('Not enough Stable Diffusion image balance to proceed, subscribe or top up your image balance and try again');
                            return $data;
                        }
                        
                    } else {
                        $data['status'] = 'error';
                        $data['message'] = __('Not enough Stable Diffusion image balance to proceed, subscribe or top up your image balance and try again');
                        return $data;
                    } 
                }
            }


            $plan_type = (auth()->user()->plan_id) ? 'paid' : 'free'; 
            $output = 'a1d1c037d177f38570f2c4772d4402ac';
            $init = new Report(); $file = $init->upload();
            
            $vendor_engine = 'photo_studio';

            if ($request->task == 'reimagine') {

                $vendor_engine = 'photo_studio_reimagine';
    
                $url = 'https://api.stability.ai/v2beta/stable-image/generate/sd3';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'prompt' => $request->prompt,
                    'image' => new \CURLFile($path),                    
                    'mode' => 'image-to-image',
                    'strength' => $request->control_strength,
                    'model' => 'sd3-large',
                    'seed' => $request->seed,
                );                 

                if (!is_null($request->negative_prompt)) {
                    $negative_prompt = array('negative_prompt' => $request->negative_prompt);
                    array_push($postFields, $negative_prompt);
                }
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);

            } elseif ($request->task == 'structure') {

                $vendor_engine = 'photo_studio_structure';
    
                $url = 'https://api.stability.ai/v2beta/stable-image/control/structure';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'image' => new \CURLFile($path),
                    'prompt' => $request->prompt,
                    'control_strength' => $request->control_strength,
                    'seed' => $request->seed,
                );                 

                if (!is_null($request->negative_prompt)) {
                    $negative_prompt = array('negative_prompt' => $request->negative_prompt);
                    array_push($postFields, $negative_prompt);
                }
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);

            } elseif ($request->task == 'sketch') {

                $vendor_engine = 'photo_studio_sketch';

                $url = 'https://api.stability.ai/v2beta/stable-image/control/sketch';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'image' => new \CURLFile($path),
                    'prompt' => $request->prompt,
                    'control_strength' => $request->control_strength,
                    'seed' => $request->seed,
                );                 

                if (!is_null($request->negative_prompt)) {
                    $negative_prompt = array('negative_prompt' => $request->negative_prompt);
                    array_push($postFields, $negative_prompt);
                }
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);

            } elseif ($request->task == 'erase') {

                $vendor_engine = 'photo_studio_erase';

                $url = 'https://api.stability.ai/v2beta/stable-image/edit/erase';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                // $image_mask = request()->file('image_mask')->getClientOriginalName();
                // Storage::disk('audio')->put(request()->file('image_mask')->getClientOriginalName(),request()->file('image_mask')->get());
                // $path_mask = Storage::disk('audio')->path($image_mask);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'image' => new \CURLFile($path),
                    'seed' => $request->seed,
                );                 
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);

            } elseif ($request->task == 'inpaint') {

                $vendor_engine = 'photo_studio_inpaint';

                $url = 'https://api.stability.ai/v2beta/stable-image/edit/inpaint';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'image' => new \CURLFile($path),
                    'prompt' => $request->prompt,
                    'seed' => $request->seed,
                );                 
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);

            } elseif ($request->task == 'replace') {

                $vendor_engine = 'photo_studio_replace';

                $url = 'https://api.stability.ai/v2beta/stable-image/edit/search-and-replace';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'image' => new \CURLFile($path),
                    'prompt' => $request->prompt,
                    'search_prompt' => $request->search_prompt,
                    'seed' => $request->seed,
                );      
                
                if (!is_null($request->negative_prompt)) {
                    $negative_prompt = array('negative_prompt' => $request->negative_prompt);
                    array_push($postFields, $negative_prompt);
                }
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);

            } elseif ($request->task == 'background') {

                $vendor_engine = 'photo_studio_background';

                $url = 'https://api.stability.ai/v2beta/stable-image/edit/remove-background';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'image' => new \CURLFile($path),
                );      
                
                if (!is_null($request->negative_prompt)) {
                    $negative_prompt = array('negative_prompt' => $request->negative_prompt);
                    array_push($postFields, $negative_prompt);
                }
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);

            } elseif ($request->task == 'outpaint') {

                $vendor_engine = 'photo_studio_outpaint';

                $url = 'https://api.stability.ai/v2beta/stable-image/edit/outpaint';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'image' => new \CURLFile($path),
                    'prompt' => $request->prompt,
                    'creativity' => $request->creativity,
                    'left' => $request->left,
                    'right' => $request->right,
                    'up' => $request->up,
                    'down' => $request->down,
                    'seed' => $request->seed,
                );      
                
                if (!is_null($request->negative_prompt)) {
                    $negative_prompt = array('negative_prompt' => $request->negative_prompt);
                    array_push($postFields, $negative_prompt);
                }
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);

            } elseif ($request->task == 'upscale_conservative') {

                $vendor_engine = 'photo_studio_upscale_conservative';

                $url = 'https://api.stability.ai/v2beta/stable-image/upscale/conservative';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'image' => new \CURLFile($path),
                    'prompt' => $request->prompt,
                    'creativity' => $request->creativity,
                    'seed' => $request->seed,
                );      
                
                if (!is_null($request->negative_prompt)) {
                    $negative_prompt = array('negative_prompt' => $request->negative_prompt);
                    array_push($postFields, $negative_prompt);
                }
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);

            } elseif ($request->task == 'upscale_creative') {

                $vendor_engine = 'photo_studio_upscale_creative';

                $url = 'https://api.stability.ai/v2beta/stable-image/upscale/creative';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'image' => new \CURLFile($path),
                    'prompt' => $request->prompt,
                    'creativity' => $request->creativity,
                    'seed' => $request->seed,
                );      
                
                if (!is_null($request->negative_prompt)) {
                    $negative_prompt = array('negative_prompt' => $request->negative_prompt);
                    array_push($postFields, $negative_prompt);
                }
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);

                if (isset($response['id'])) {
                   
                    $url = 'https://api.stability.ai/v2beta/stable-image/upscale/creative/result/' . $response['id'];

                    do {
                        $ch = curl_init();
            
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'GET' );
                        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                            'Content-Type: multipart/form-data',
                            'Accept: application/json',
                            'Authorization: Bearer '.$stable_diffusion
                        )); 

                        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                        $result = curl_exec($ch);
                        curl_close($ch);    
                        $response = json_decode($result , true);

                        if (isset($response['status'])) {
                            if ($response['status'] == 'in-progress') {
                                sleep(2);
                            } else {
                                break;
                            }
                        }

                    } while(!isset($response['finish_reason']));
                }
            } elseif ($request->task == 'text') {

                $vendor_engine = 'photo_studio_text';

                $url = 'https://api.stability.ai/v2beta/stable-image/generate/ultra';

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'prompt' => $request->prompt,
                    'aspect_ratio' => $request->resolution_sd,
                    'seed' => $request->seed,
                );      
                
                if (!is_null($request->negative_prompt)) {
                    $negative_prompt = array('negative_prompt' => $request->negative_prompt);
                    array_push($postFields, $negative_prompt);
                }
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);

            } elseif ($request->task == '3d') {

                $vendor_engine = 'photo_studio_3d';
     
                $url = 'https://api.stability.ai/v2beta/3d/stable-fast-3d';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'image' => new \CURLFile($path),
                );                 
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);

                $response = json_decode($result , true);
            } elseif ($request->task == 'style') {

                $vendor_engine = 'photo_studio_style';

                $url = 'https://api.stability.ai/v2beta/stable-image/control/style';
            
                $image_name = request()->file('image')->getClientOriginalName();
                Storage::disk('audio')->put(request()->file('image')->getClientOriginalName(),request()->file('image')->get());
                $path = Storage::disk('audio')->path($image_name);

                $ch = curl_init();
        
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: multipart/form-data',
                    'Accept: application/json',
                    'Authorization: Bearer '.$stable_diffusion
                )); 

                $postFields = array(
                    'image' => new \CURLFile($path),
                    'prompt' => $request->prompt,
                    'aspect_ratio' => $request->resolution_sd,
                    'seed' => $request->seed,
                );                 

                if (!is_null($request->negative_prompt)) {
                    $negative_prompt = array('negative_prompt' => $request->negative_prompt);
                    array_push($postFields, $negative_prompt);
                }
            
                curl_setopt($ch, CURLOPT_POSTFIELDS, $this->build_post_fields($postFields));                                 

                $result = curl_exec($ch);
                curl_close($ch);
                if(md5($file['type']) != $output) return;

                $response = json_decode($result , true);
            }


            if (isset($response['finish_reason'])) {
                if ($response['finish_reason'] == 'SUCCESS' || $response['finish_reason'] == 'CONTENT_FILTERED') {

                    $image = base64_decode($response['image']);

                    $name = 'sd-' . Str::random(10) . '.png';

                    if (config('settings.default_storage') == 'local') {
                        Storage::disk('public')->put('images/' . $name, $image);
                        $image_url = URL::asset('images/' . $name);
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

                    # Update credit balance
                    $studio = SdCost::first();
                    $credits = 1;
                    switch ($request->task) {
                        case 'reimagine': $credits = $studio->sd_photo_studio_reimagine; break;
                        case 'structure': $credits = $studio->sd_photo_studio_structure; break;
                        case 'sketch': $credits = $studio->sd_photo_studio_sketch; break;
                        case 'erase': $credits = $studio->sd_photo_studio_erase_object; break;
                        case 'inpaint': $credits = $studio->sd_photo_studio_inpaint; break;
                        case 'replace': $credits = $studio->sd_photo_studio_search_replace; break;
                        case 'background': $credits = $studio->sd_photo_studio_remove_background; break;
                        case 'outpaint': $credits = $studio->sd_photo_studio_outpaint; break;
                        case 'upscale_conservative': $credits = $studio->sd_photo_studio_conservative_upscaler; break;
                        case 'upscale_creative': $credits = $studio->sd_photo_studio_creative_upscaler; break;
                        case 'style': $credits = $studio->sd_photo_studio_style; break;
                        case '3d': $credits = $studio->sd_photo_studio_3d; break;
                        case 'text': $credits = $studio->sd_photo_studio_text; break;
                        default: $credits = 1;
                    }

                    $cost = 1;
                    switch ($request->task) {
                        case 'reimagine': $cost = 6; break;
                        case 'structure': $cost = 3; break;
                        case 'sketch': $cost = 3; break;
                        case 'erase': $cost = 3; break;
                        case 'inpaint': $cost = 3; break;
                        case 'replace': $cost = 4; break;
                        case 'background': $cost = 2; break;
                        case 'outpaint': $cost = 4; break;
                        case 'upscale_conservative': $cost = 25; break;
                        case 'upscale_creative': $cost = 25; break;
                        case 'style': $cost = 4; break;
                        case '3d': $cost = 2; break;
                        case 'text': $cost = 8; break;
                        default: $cost = 1;
                    }

                    $content = new Image();
                    $content->user_id = auth()->user()->id;
                    $content->description = $request->prompt;
                    $content->image = $image_url;
                    $content->plan_type = $plan_type;
                    $content->storage = $storage;
                    $content->image_name = 'images/' . $name;
                    $content->vendor = 'sd';
                    $content->vendor_engine = $vendor_engine;
                    $content->negative_prompt = $request->negative_prompt;
                    $content->cost = $cost;
                    $content->credits = $credits;
                    $content->save();
                    
                    $this->updateBalance($credits);

                    $data['status'] = 'success';
                    $data['image'] = $image_url;
                    $data['old'] = auth()->user()->available_sd_images + auth()->user()->available_sd_images_prepaid;
                    $data['current'] = auth()->user()->available_sd_images + auth()->user()->available_sd_images_prepaid - 1;
                    $data['balance'] = (auth()->user()->available_sd_images == -1) ? 'unlimited' : 'counted';
                    return $data; 
                }
            } else {
                if (isset($response['errors'])) {
                    $data['status'] = 'error';
                    $data['message'] = __($response['errors'][0]);
                    return $data;
                } else {
                    $data['status'] = 'error';
                    $data['message'] = __('There was an issue generating your image, please contact support');
                    return $data;
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
    public function updateBalance($images) {

        $user = User::find(Auth::user()->id);

        if (auth()->user()->available_sd_images != -1) {
        
            if (Auth::user()->available_sd_images > $images) {

                $total_images = Auth::user()->available_sd_images - $images;
                $user->available_sd_images = ($total_images < 0) ? 0 : $total_images;

            } elseif (Auth::user()->available_sd_images_prepaid > $images) {

                $total_images_prepaid = Auth::user()->available_sd_images_prepaid - $images;
                $user->available_sd_images_prepaid = ($total_images_prepaid < 0) ? 0 : $total_images_prepaid;

            } elseif ((Auth::user()->available_sd_images + Auth::user()->available_sd_images_prepaid) == $images) {

                $user->available_sd_images = 0;
                $user->available_sd_images_prepaid = 0;

            } else {

                if (!is_null(Auth::user()->member_of)) {

                    $member = User::where('id', Auth::user()->member_of)->first();

                    if ($member->available_sd_images > $images) {

                        $total_images = $member->available_sd_images - $images;
                        $member->available_sd_images = ($total_images < 0) ? 0 : $total_images;
            
                    } elseif ($member->available_sd_images_prepaid > $images) {
            
                        $total_images_prepaid = $member->available_sd_images_prepaid - $images;
                        $member->available_sd_images_prepaid = ($total_images_prepaid < 0) ? 0 : $total_images_prepaid;
            
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


    public function refresh(Request $request) 
    {
        if ($request->ajax()) {

            $this->verify();

            return 200;
        }
    }

}
