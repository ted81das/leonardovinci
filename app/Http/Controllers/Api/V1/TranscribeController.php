<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Orhanerday\OpenAi\OpenAi;
use App\Models\SubscriptionPlan;
use App\Models\Transcript;
use App\Models\Workbook;
use App\Models\User;
use App\Models\ApiKey;


class TranscribeController extends Controller
{
    /**
     * Transcribe audio files
     *
     * @OA\Post(
     *      path="/api/v1/speech/transcribe",
     *      operationId="speechTranscribe",
     *      tags={"AI Speech to Text"},
     *      summary="Speech to Text transcribe feature",
     *      description="Transcribe user uploaded audio files in various formats and languages",
     *      security={{ "passport": {} }},
     *      @OA\RequestBody(
     *          required=true,
     *          description="User data",
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  type="object",
     *                  @OA\Property(
     *                      property="audiofile",
     *                      description="Target audio file that needs to be transcribed. Maximum supported audio file size by OpenAI is 25MB.",
     *                      type="binary",
     *                      format="binary"
     *                  ),
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Transcribed successfully",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="text", type="string", description="Text result of the audio file"),
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
     *      ),
     *      
     *      @OA\Response(
     *          response=401,
     *          description="Unauthorized: User not authenticated",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not Allowed. Restrictions applied.",
     *      ),
     *      @OA\Response(
     *          response=419,
     *          description="Validation error or unsupported file extension",
     *      ),
     * )
    */
	public function transcribe(Request $request) 
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

        $plan_type = (Auth::user()->group == 'subscriber') ? 'paid' : 'free';


        if (request()->has('audiofile')) {

            # Count minutes based on vendor requirements
            // $audio_length = ((float)request('audiolength') / 60);    
            // $audio_length = number_format((float)$audio_length, 3, '.', '');


            // # Check if user has minutes available to proceed
            // if (auth()->user()->available_minutes != -1) {
            //     if ((auth()->user()->available_minutes + auth()->user()->available_minutes_prepaid) < $audio_length) {
            //         if (!is_null(auth()->user()->member_of)) {
            //             if (auth()->user()->member_use_credits_speech) {
            //                 $member = User::where('id', auth()->user()->member_of)->first();
            //                 if (($member->available_minutes + $member->available_minutes_prepaid) < $audio_length) {
            //                     return response()->json(['error' => __('Not enough available minutes to process')], 405);
            //                 }
            //             } else {
            //                 return response()->json(['error' => __('Not enough available minutes to process')], 405);
            //             }
                        
            //         } else {
            //             return response()->json(['error' => __('Not enough available minutes to process')], 405);
            //         } 
            //     } else {
            //         $this->updateBalance($audio_length);
            //     } 
            // }
    
            $audio = request()->file('audiofile');
            $format = $audio->getClientOriginalExtension();
            $file_name = $audio->getClientOriginalName();
            $size = $audio->getSize();
            $file_size = $this->formatBytes($size);
            $name = Str::random(10) . '.' . $format;

            
            if ($size > (config('settings.whisper_max_audio_size') * 1048576)) {
                return response()->json(['error' => __('File is too large, maximum allowed audio file size is') . config('settings.whisper_max_audio_size') . 'MB'], 405);
            } 
            
            if (config('settings.whisper_default_storage') == 'local') {
                $audio_url = $audio->store('transcribe','public');
            } elseif (config('settings.whisper_default_storage') == 'aws') {
                Storage::disk('s3')->put($name, file_get_contents($audio));
                $audio_url = Storage::disk('s3')->url($name);
            } elseif (config('settings.whisper_default_storage') == 'r2') {
                Storage::disk('r2')->put($name, file_get_contents($audio));
                $audio_url = Storage::disk('r2')->url($name);
            } elseif (config('settings.whisper_default_storage') == 'wasabi') {
                Storage::disk('wasabi')->put($name, file_get_contents($audio));
                $audio_url = Storage::disk('wasabi')->url($name);
            } elseif (config('settings.whisper_default_storage') == 'gcp') {
                Storage::disk('gcs')->put($name, file_get_contents($audio));
                Storage::disk('gcs')->setVisibility($name, 'public');
                $audio_url = Storage::disk('gcs')->url($name);
                $storage = 'gcp';
            } elseif (config('settings.whisper_default_storage') == 'storj') {
                Storage::disk('storj')->put($name, file_get_contents($audio), 'public');
                Storage::disk('storj')->setVisibility($name, 'public');
                $audio_url = Storage::disk('storj')->temporaryUrl($name, now()->addHours(167));
                $storage = 'storj';                        
            } elseif (config('settings.whisper_default_storage') == 'dropbox') {
                Storage::disk('dropbox')->put($name, file_get_contents($audio));
                $audio_url = Storage::disk('dropbox')->url($name);
                $storage = 'dropbox';
            }
        }

        # Audio Format
        if ($format == 'mp3') {
            $audio_type = 'audio/mpeg';
        } elseif ($format == 'ogg') {
            $audio_type = 'audio/ogg';
        } elseif($format == 'wav') {
            $audio_type = 'audio/wav';
        } elseif($format == 'webm') {
            $audio_type = 'audio/webm';
        } else {
            $audio_type = 'audio/mpeg';
        }
        
        if (config('settings.whisper_default_storage') == 'local') {
            $file = curl_file_create($audio_url);
        } else {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_URL, $audio_url);
            $content = curl_exec($curl);
            Storage::disk('public')->put('transcribe/' . $file_name, $content);
            $file = curl_file_create('transcribe/' . $file_name);
            curl_close($curl);
            
        }


        $complete = $open_ai->transcribe([
            'model' => 'whisper-1',
            'file' => $file,
            'prompt' => request('description'),
            'language' => request('language')
        ]);
        
        $response = json_decode($complete , true);

        if (isset($response['text'])) {

            $text = $response['text'];

            # Delete temp file
            if (config('settings.whisper_default_storage') != 'local') {
                if (Storage::disk('public')->exists('transcribe/' . $file_name)) {
                    Storage::disk('public')->delete('transcribe/' . $file_name);
                }
            }

            $words = count(explode(' ', ($text)));
                
            $transcript = new Transcript();
            $transcript->user_id = auth()->user()->id;
            $transcript->transcript = $text;
            $transcript->title = request('document');
            $transcript->workbook = request('project');
            $transcript->description = request('description');
            $transcript->task = request('task');
            $transcript->format = $format;
            $transcript->words = $words;
            $transcript->size = $file_size;
            $transcript->file_name = $file_name;
            $transcript->temp_name = $name;
            $transcript->length = request('audiolength');
            $transcript->plan_type = $plan_type;
            $transcript->url = $audio_url;
            $transcript->audio_type = $audio_type;
            $transcript->storage = config('settings.whisper_default_storage');
            $transcript->save();

            return response()->json(['status' => true, 'text' => $text], 201);

        } else {

            if (isset($response['error']['message'])) {
                $message = $response['error']['message'];
                return response()->json(['error' => $message], 405);
            } else {
                return response()->json(['error' => __('There is an issue with your openai account settings')], 405);
            }

        }

	}


    /**
	*
	* Update user minutes balance
	* @param - total words generated
	* @return - confirmation
	*
	*/
    public function updateBalance($minutes) {

        $user = User::find(Auth::user()->id);

        if (auth()->user()->available_minutes != -1) {
            
            if (Auth::user()->available_minutes > $minutes) {

                $total_minutes = Auth::user()->available_minutes - $minutes;
                $user->available_minutes = ($total_minutes < 0) ? 0 : $total_minutes;
    
            } elseif (Auth::user()->available_minutes_prepaid > $minutes) {
    
                $total_minutes_prepaid = Auth::user()->available_minutes_prepaid - $minutes;
                $user->available_minutes_prepaid = ($total_minutes_prepaid < 0) ? 0 : $total_minutes_prepaid;
    
            } elseif ((Auth::user()->available_minutes + Auth::user()->available_minutes_prepaid) == $minutes) {
    
                $user->available_minutes = 0;
                $user->available_minutes_prepaid = 0;
    
            } else {
    
                if (!is_null(Auth::user()->member_of)) {
    
                    $member = User::where('id', Auth::user()->member_of)->first();
    
                    if ($member->available_minutes > $minutes) {
    
                        $total_minutes = $member->available_minutes - $minutes;
                        $member->available_minutes = ($total_minutes < 0) ? 0 : $total_minutes;
            
                    } elseif ($member->available_minutes_prepaid > $minutes) {
            
                        $total_minutes_prepaid = $member->available_minutes_prepaid - $minutes;
                        $member->available_minutes_prepaid = ($total_minutes_prepaid < 0) ? 0 : $total_minutes_prepaid;
            
                    } elseif (($member->available_minutes + $member->available_minutes_prepaid) == $minutes) {
            
                        $member->available_minutes = 0;
                        $member->available_minutes_prepaid = 0;
            
                    } else {
                        $remaining = $minutes - $member->available_minutes;
                        $member->available_minutes = 0;
        
                        $prepaid_left = $member->available_minutes_prepaid - $remaining;
                        $member->available_minutes_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                    }
    
                    $member->update();
    
                } else {
                    $remaining = $minutes - Auth::user()->available_minutes;
                    $user->available_images = 0;
    
                    $prepaid_left = Auth::user()->available_minutes_prepaid - $remaining;
                    $user->available_minutes_prepaid = ($prepaid_left < 0) ? 0 : $prepaid_left;
                }
            }    
        }
    
        $user->update();

    }




}
