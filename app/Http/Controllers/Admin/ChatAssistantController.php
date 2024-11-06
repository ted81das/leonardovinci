<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ChatCategory;
use App\Models\CustomChat;
use App\Models\CustomChatFile;
use App\Models\FineTuneModel;
use Yajra\DataTables\DataTables;

class ChatAssistantController extends Controller
{

    private $client;

    public function __construct()
    {
        $this->client = \OpenAI::factory()
            ->withApiKey(config('services.openai.key'))
            ->withHttpHeader('OpenAI-Beta', 'assistants=v2')
            ->make();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = CustomChat::where('type', 'custom')->orderBy('group', 'asc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function($row){
                    $actionBtn = '<div>      
                                    <a href="'. route("admin.chat.assistant.show", $row["id"] ). '"><i class="fa fa-edit table-action-buttons view-action-button" title="'. __('Update Chat Assistant') .'"></i></a>      
                                    <a class="activateButton" id="' . $row["id"] . '" type="' . $row['type'] . '" href="#"><i class="fa fa-check table-action-buttons request-action-button" title="'. __('Activate Chat Assistant') .'"></i></a>
                                    <a class="deactivateButton" id="' . $row["id"] . '" type="' . $row['type'] . '" href="#"><i class="fa fa-close table-action-buttons delete-action-button" title="'. __('Deactivate Chat Assistant') .'"></i></a>  
                                    <a class="deleteTemplate" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete Chat Assistant') .'"></i></a> 
                                </div>';
                    
                    return $actionBtn;
                })
                ->addColumn('created-on', function($row){
                    $created_on = '<span>'.date_format($row["updated_at"], 'd M Y').'</span>';
                    return $created_on;
                })
                ->addColumn('custom-status', function($row){
                    $status = ($row['status']) ? 'active' : 'deactive'; 
                    $custom_voice = '<span class="cell-box status-'. $status.'">'.ucfirst($status).'</span>';
                    return $custom_voice;
                })
                ->addColumn('custom-group', function($row){
                    $group = '<span>'.ucfirst($row['group']).'</span>';
                    return $group;
                })
                ->addColumn('custom-avatar', function($row){
                    if ($row['logo']) {
                        $path = URL::asset($row['logo']);
                    } else {
                        $path = theme_url('img/users/avatar.jpg');
                    }

                    $avatar = '<div class="widget-user-image-sm overflow-hidden"><img alt="Voice Avatar" class="rounded-circle" src="' . $path . '"></div>';
                    return $avatar;
                })
                ->addColumn('custom-package', function($row){
                    switch ($row['category']) {
                        case 'all':
                            $package = '<span class="cell-box plan-regular">' . __('All') .'</span>';
                            break;
                        case 'free':
                            $package = '<span class="cell-box plan-free">' . __('Free') .'</span>';
                            break;
                        case 'professional':
                            $package = '<span class="cell-box plan-professional">' . __('Professional') .'</span>';
                            break;
                        case 'premium':
                            $package = '<span class="cell-box plan-premium">' . __('Premium') .'</span>';
                            break;
                        default:
                            $package = '<span class="cell-box plan-regular">' . __('Standard') .'</span>';
                            break;
                    }                      
                    return $package;
                })
                ->addColumn('custom-model', function($row){
                    switch ($row['model']) {
                        case 'gpt-3.5-turbo-0125':
                            $model = '<span>GPT 3.5 Turbo</span>';                            
                            break;
                        case 'gpt-4-0125-preview':
                            $model = '<span>GPT 4 Turbo</span>';                            
                            break;
                        case 'gpt-4':
                            $model = '<span>GPT 4</span>';                            
                            break;
                        case 'gpt-4o':
                            $model = '<span>GPT 4o</span>';                            
                            break;
                        case 'gpt-4o-mini':
                            $model = '<span>GPT 4o mini</span>';                            
                            break;
                        case 'gpt-4-turbo-2024-04-09':
                            $model = '<span>GPT 4 Turbo Vision</span>';                            
                            break;
                        default:
                            $model = '<span>Fine Tuned Model</span>'; 
                            break;
                    }
                    
                    return $model;
                })
                ->rawColumns(['actions', 'created-on', 'custom-status', 'custom-avatar', 'custom-package', 'custom-group', 'custom-model'])
                ->make(true);
                    
        }

        $categories = ChatCategory::orderBy('name', 'asc')->get();

        return view('admin.davinci.chats.assistant.index', compact('categories'));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = ChatCategory::orderBy('name', 'asc')->get();
        $fine_tunes = FineTuneModel::all();

        return view('admin.davinci.chats.assistant.create', compact('categories', 'fine_tunes'));
        
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'name' => 'required',
            'instructions' => 'required',
        ]);    
        
        $status = (isset($request->activate)) ? true : false;
        $retrieval = (isset($request->retrieval)) ? true : false;
        $code = (isset($request->code)) ? true : false;
        $upload = (isset($request->upload)) ? true : false;
        $has_file = false;
        $uploaded_file = '';

        if ($retrieval && $code) {
            $tools = [
                [ 'type' => 'code_interpreter'],
                [ 'type' => 'file_search' ]
            ];
        } elseif ($retrieval) {
            $tools = [
                [ 'type' => 'file_search' ]
            ];
        } elseif ($code) {
            $tools = [
                [ 'type' => 'code_interpreter'],
            ];
        } else {
            $tools = [];
        }

        if (request()->has('logo')) {
            
            $image = request()->file('logo');

            $name = Str::random(20);
            
            $folder = '/chats/custom/';
            
            $avatarPath = $folder . $name . '.' . $image->getClientOriginalExtension();

            $imageTypes = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array(Str::lower($image->getClientOriginalExtension()), $imageTypes)) {
                toastr()->error(__('Chat avatar image must be in png, jpeg or webp formats'));
                return redirect()->back();
            } else {
                $this->uploadImage($image, $folder, 'public', $name);
            }
            
        } else {
            $avatarPath = '/chats/custom/avatar.webp';
        }

        if (request()->has('file')) {

            $file = request()->file('file');

            $imageTypes = ['c', 'cpp', 'doc', 'docx', 'html', 'java', 'md', 'php', 'pptx', 'py', 'rb', 'tex', 'js', 'ts', 'pdf', 'txt', 'json'];
            if (!in_array(Str::lower($file->getClientOriginalExtension()), $imageTypes)) {
                toastr()->error(__('Unsupported file format was selected, make sure to upload a file with a supported file format listed below'));
                return redirect()->back();

            } else {

                $original_name = $file->getClientOriginalName();
                $name = Str::random(20);            
                $folder = '/uploads/assistant/';            
                $filePath = $folder . $name . '.' . $file->getClientOriginalExtension();
                $this->uploadImage($file, $folder, 'public', $name);

                $uploaded_file = $this->client->files()->upload([
                    'purpose' => 'assistants',
                    'file' => fopen( public_path() . $filePath, 'rb'),
                ]);

                $has_file = true;
            }
        
        }

        $vector = $this->createVectorStore();

        $url = 'https://api.openai.com/v1/assistants';

        $ch = curl_init();

        if ($has_file) {
            $vector_file = $this->addFile($vector['id'],  $uploaded_file['id']);

            $data = array(
                'instructions' => $request->instructions,
                'name' => $request->name,
                'tools' => $tools,
                'model' => $request->model,
                "tool_resources" => ["file_search" => ["vector_store_ids" => [$vector['id']]],]
            ); 
                        
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));   
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'OpenAI-Beta: assistants=v2',
                'Authorization: Bearer ' . config('services.openai.key'),
            )); 
            
        } else {
            $data = array(
                'instructions' => $request->instructions,
                'name' => $request->name,
                'model' => $request->model,
                'tools' => $tools,
            ); 
                        
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));   
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'OpenAI-Beta: assistants=v2',
                'Authorization: Bearer ' . config('services.openai.key'),
            )); 
        }

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result , true);

        $template = new CustomChat([
            'user_id' => auth()->user()->id,
            'description' => $request->character,
            'status' => $status,
            'chat_code' => $response['id'],
            'name' => $request->name,
            'group' => $request->group,
            'prompt' => $request->instructions,
            'sub_name' => $request->sub_name,
            'logo' => $avatarPath,
            'model' => $request->model,
            'code_interpreter' => $code,
            'retrieval' => $retrieval,
            'upload' => $upload,
            'type' => 'custom',
            'model_mode' => 'fixed',
            'category' => $request->category,
            'vector_store' => $vector['id'],
        ]); 
        
        $template->save();
        
        if ($has_file) {
            $custom_file = new CustomChatFile([
                'chat_id' => $template->id,
                'vector_id' => $vector['id'],
                'file_id' => $uploaded_file['id'],
                'name' => $original_name,
                'url' => $filePath,
            ]);

            $custom_file->save();
        }

        toastr()->success(__('Custom Chat Assistant was successfully created'));
        if ($has_file) {
            toastr()->success(__('Uploaded files will take few minutes to be processed first'));
        }
        return redirect()->back();       
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, CustomChat $id)
    {
        if ($request->ajax()) {
            $data = CustomChat::where('type', 'custom')->orderBy('group', 'asc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function($row){
                    $actionBtn = '<div>      
                                    <a href="'. route("admin.chat.assistant.show", $row["id"] ). '"><i class="fa fa-edit table-action-buttons view-action-button" title="'. __('Update Chat Assistant') .'"></i></a>      
                                    <a class="activateButton" id="' . $row["id"] . '" type="' . $row['type'] . '" href="#"><i class="fa fa-check table-action-buttons request-action-button" title="'. __('Activate Chat Assistant') .'"></i></a>
                                    <a class="deactivateButton" id="' . $row["id"] . '" type="' . $row['type'] . '" href="#"><i class="fa fa-close table-action-buttons delete-action-button" title="'. __('Deactivate Chat Assistant') .'"></i></a>  
                                    <a class="deleteTemplate" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete Chat Assistant') .'"></i></a> 
                                </div>';
                    
                    return $actionBtn;
                })
                ->addColumn('created-on', function($row){
                    $created_on = '<span>'.date_format($row["updated_at"], 'd M Y').'</span>';
                    return $created_on;
                })
                ->addColumn('custom-status', function($row){
                    $status = ($row['status']) ? 'active' : 'deactive'; 
                    $custom_voice = '<span class="cell-box status-'. $status.'">'.ucfirst($status).'</span>';
                    return $custom_voice;
                })
                ->addColumn('custom-group', function($row){
                    $group = '<span>'.ucfirst($row['group']).'</span>';
                    return $group;
                })
                ->addColumn('custom-avatar', function($row){
                    if ($row['logo']) {
                        $path = URL::asset($row['logo']);
                    } else {
                        $path = URL::asset('img/users/avatar.jpg');
                    }

                    $avatar = '<div class="widget-user-image-sm overflow-hidden"><img alt="Voice Avatar" class="rounded-circle" src="' . $path . '"></div>';
                    return $avatar;
                })
                ->addColumn('custom-package', function($row){
                    switch ($row['category']) {
                        case 'all':
                            $package = '<span class="cell-box plan-regular">' . __('All') .'</span>';
                            break;
                        case 'free':
                            $package = '<span class="cell-box plan-free">' . __('Free') .'</span>';
                            break;
                        case 'professional':
                            $package = '<span class="cell-box plan-professional">' . __('Professional') .'</span>';
                            break;
                        case 'premium':
                            $package = '<span class="cell-box plan-premium">' . __('Premium') .'</span>';
                            break;
                        default:
                            $package = '<span class="cell-box plan-regular">' . __('Standard') .'</span>';
                            break;
                    }                      
                    return $package;
                })
                ->addColumn('custom-model', function($row){
                    switch ($row['model']) {
                        case 'gpt-3.5-turbo-0125':
                            $model = '<span>GPT 3.5 Turbo</span>';                            
                            break;
                        case 'gpt-4-0125-preview':
                            $model = '<span>GPT 4 Turbo</span>';                            
                            break;
                        case 'gpt-4-turbo-2024-04-09':
                            $model = '<span>GPT 4 Turbo Vision</span>';                            
                            break;
                    }
                    
                    return $model;
                })
                ->rawColumns(['actions', 'created-on', 'custom-status', 'custom-avatar', 'custom-package', 'custom-group', 'custom-model'])
                ->make(true);
                    
        }

        session()->put('chatbot_id', $id->id);

        $categories = ChatCategory::orderBy('name', 'asc')->get();
        $fine_tunes = FineTuneModel::all();

        return view('admin.davinci.chats.assistant.edit', compact('id', 'categories', 'fine_tunes'));
        
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function files(Request $request)
    {
        $id = session()->get('chatbot_id');

        if ($request->ajax()) {
            $data = CustomChatFile::where('chat_id', $id)->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function($row){
                    $actionBtn = '<div>      
                                    <a href="'. URL::asset($row['url']).'" download><i class="fa fa-download table-action-buttons view-action-button" title="'. __('Download File') .'"></i></a>                                         
                                    <a class="deleteFile" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete File') .'"></i></a> 
                                </div>';
                    
                    return $actionBtn;
                })
                ->addColumn('created-on', function($row){
                    $created_on = '<span>'.date_format($row["updated_at"], 'd M Y').'</span>';
                    return $created_on;
                })
                ->rawColumns(['actions', 'created-on'])
                ->make(true);        
        }

        $chat = CustomChat::where('id', $id)->first();

        return view('admin.davinci.chats.assistant.files', compact('chat'));
    }


     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CustomChat $id)
    {        
        request()->validate([
            'name' => 'required',
            'instructions' => 'required',
        ]); 

        $status = (isset($request->activate)) ? true : false;
        $retrieval = (isset($request->retrieval)) ? true : false;
        $code = (isset($request->code)) ? true : false;
        $upload = (isset($request->upload)) ? true : false;
        $has_file = false;

        if ($retrieval && $code) {
            $tools = [
                [ 'type' => 'code_interpreter'],
                [ 'type' => 'file_search' ]
            ];
        } elseif ($retrieval) {
            $tools = [
                [ 'type' => 'file_search' ]
            ];
        } elseif ($code) {
            $tools = [
                [ 'type' => 'code_interpreter'],
            ];
        } else {
            $tools = [];
        }

        if (request()->has('logo')) {
            
            $image = request()->file('logo');

            $name = Str::random(20);
            
            $folder = '/chats/custom/';
            
            $avatarPath = $folder . $name . '.' . $image->getClientOriginalExtension();

            $imageTypes = ['jpg', 'jpeg', 'png', 'webp'];
            if (!in_array(Str::lower($image->getClientOriginalExtension()), $imageTypes)) {
                toastr()->error(__('Chat avatar image must be in png, jpeg or webp formats'));
                return redirect()->back();
            } else {
                $this->uploadImage($image, $folder, 'public', $name);
            }
            
        } else {
            $avatarPath = $id->logo;
        }

        if (request()->has('file')) {

            $file = request()->file('file');

            $imageTypes = ['c', 'cpp', 'doc', 'docx', 'html', 'java', 'md', 'php', 'pptx', 'py', 'rb', 'tex', 'js', 'ts', 'pdf', 'txt', 'json'];
            if (!in_array(Str::lower($file->getClientOriginalExtension()), $imageTypes)) {
                toastr()->error(__('Uploaded training files must be in pdf, csv, json, jsonl or txt formats'));
                return redirect()->back();
            } else {

                $original_name = $file->getClientOriginalName();
                $name = Str::random(20);            
                $folder = '/uploads/assistant/';            
                $filePath = $folder . $name . '.' . $file->getClientOriginalExtension();
                $this->uploadImage($file, $folder, 'public', $name);

                $uploaded_file = $this->client->files()->upload([
                    'purpose' => 'assistants',
                    'file' => fopen( public_path() . $filePath, 'rb'),
                ]);

                $has_file = true;
            }
        }

        $url = 'https://api.openai.com/v1/assistants/' . $id->chat_code;

        $ch = curl_init();

        $data = array(
            'instructions' => $request->instructions,
            'name' => $request->name,
            'model' => $request->model,
            'tools' => $tools,
        ); 
                    
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));   
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'OpenAI-Beta: assistants=v2',
            'Authorization: Bearer ' . config('services.openai.key'),
        )); 

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result , true);


        if ($has_file) {
            $vector_file = $this->addFile($id->vector_store,  $uploaded_file['id']);
        } 
    

        if ($id->user_id == auth()->user()->id) {

            $id->update([
                'description' => $request->character,
                'status' => $status,
                'name' => $request->name,
                'group' => $request->group,
                'prompt' => $request->instructions,
                'logo' => $avatarPath,
                'code_interpreter' => $code,
                'retrieval' => $retrieval,
                'upload' => $upload,
                'sub_name' => $request->sub_name,
                'model' => $request->model,
                'type' => 'custom',
                'model_mode' => 'fixed',
                'category' => $request->category,
            ]); 

            if ($has_file) {
                $custom_file = new CustomChatFile([
                    'chat_id' => $id->id,
                    'vector_id' => $id->vector_store,
                    'file_id' => $uploaded_file['id'],
                    'name' => $original_name,
                    'url' => $filePath,
                ]);
    
                $custom_file->save();
            }

            toastr()->success(__('Custom Chat Assistant was successfully updated'));
            if ($has_file) {
                toastr()->success(__('Uploaded files will take few minutes to be processed first'));
            }
            return redirect()->route('admin.chat.assistant');
        } else {
            toastr()->warning(__('Access denied'));
            return redirect()->back();   
        }

    }


    /**
     * Activate template
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function chatActivate(Request $request)
    {
        if ($request->ajax()) {

            $template = CustomChat::where('id', request('id'))->firstOrFail();

            if ($template->user_id == auth()->user()->id) {
                if ($template->status == true) {
                    return  response()->json(true);
                }

                $template->update(['status' => true]);

                return  response()->json('success');

            } else {
                return response()->json('error');
            }
        }
    }


    /**
     * Deactivate template.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function chatDeactivate(Request $request)
    {
        if ($request->ajax()) {

            $template = CustomChat::where('id', request('id'))->firstOrFail();

            if ($template->user_id == auth()->user()->id) {
                if ($template->status == false) {
                    return  response()->json(false);
                }
    
                $template->update(['status' => false]);
    
                return  response()->json('success');
            } else {
                return response()->json('error');
            }
            
        }
    }


     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function chatDelete(Request $request)
    {
        if ($request->ajax()) {

            $result = CustomChat::where('id', request('id'))->firstOrFail();  

            if ($result->user_id == auth()->user()->id){

                $result->delete();

                return response()->json('success');    
    
            } else{
                return response()->json('error');
            } 
        }              
    }


     /**
     * Upload user profile image
     */
    public function uploadImage(UploadedFile $file, $folder = null, $disk = 'public', $filename = null)
    {
        $name = !is_null($filename) ? $filename : Str::random(25);

        $image = $file->storeAs($folder, $name .'.'. $file->getClientOriginalExtension(), $disk);

        return $image;
    }


    public function createVectorStore()
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
            'Authorization: Bearer ' . config('services.openai.key'),
        )); 

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result , true);

        return $response;
    }


    public function addFile($vector_store_id, $file_id)
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
            'Authorization: Bearer ' . config('services.openai.key'),
        )); 

        $result = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($result , true);

        return $response;
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function fileDelete(Request $request)
    {
        if ($request->ajax()) {

            $result = CustomChatFile::where('id', request('id'))->firstOrFail();  

            if ($result){

                $url = 'https://api.openai.com/v1/vector_stores/' . $result->vector_id . '/files/' . $result->file_id; 
                $ch = curl_init();

         
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'OpenAI-Beta: assistants=v2',
                    'Authorization: Bearer ' . config('services.openai.key'),
                )); 

                $status = curl_exec($ch);
                curl_close($ch);
        
                $response = json_decode($status , true);

                $result->delete();

                return response()->json('success');    
    
            } else{
                return response()->json('error');
            } 
        }              
    }

}
