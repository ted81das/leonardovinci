<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImagePrompt;
use DataTables;

class ImagePromptController extends Controller
{

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function prompt(Request $request)
    {
        if ($request->ajax()) {
            $data = ImagePrompt::orderBy('updated_at', 'asc')->get();
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('actions', function($row){
                    $actionBtn = '<div>      
                                    <a class="editButton" href="'. route("admin.davinci.image.prompt.edit", $row["id"] ). '"><i class="fa fa-edit table-action-buttons view-action-button" title="'. __('Edit Prompt') .'"></i></a>     
                                    <a class="activateButton" id="' . $row["id"] . '" href="#"><i class="fa fa-check table-action-buttons request-action-button" title="'. __('Activate Prompt') .'"></i></a>
                                    <a class="deactivateButton" id="' . $row["id"] . '" href="#"><i class="fa fa-close table-action-buttons delete-action-button" title="'. __('Deactivate Prompt') .'"></i></a>                
                                    <a class="deleteButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete Prompt') .'"></i></a> 
                                </div>';     
                    return $actionBtn;
                })
                ->addColumn('updated-on', function($row){
                    $created_on = '<span class="font-weight-bold">'.date_format($row["updated_at"], 'd/m/Y').'</span><br><span>'.date_format($row["updated_at"], 'H:i A').'</span>';
                    return $created_on;
                })
                ->addColumn('custom-status', function($row){
                    $status = ($row['status']) ? __('Active') : __('Deactive');
                    $custom_voice = '<span class="cell-box status-'.strtolower($status).'">'. $status.'</span>';
                    return $custom_voice;
                })
                ->rawColumns(['actions', 'updated-on', 'custom-status'])
                ->make(true);
                    
        }

        return view('admin.davinci.images.prompt');
    }

    /**
     * Create new chat prompt
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function promptCreate()
    {   
        return view('admin.davinci.images.prompt-create');     
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function promptStore(Request $request)
    {   

        $prompt = new ImagePrompt([
            'status' => true,
            'title' => request('title'),
            'prompt' => request('prompt'),
        ]); 

        $prompt->save();

        toastr()->success(__('Image prompt has been successfully created'));
        return redirect()->route('admin.davinci.image.prompt');     
    }


    /**
     * Edit prompt
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function promptEdit($id)
    {   
        $prompt = ImagePrompt::where('id', $id)->first();

        return view('admin.davinci.images.prompt-edit', compact('prompt'));     
    }
    

    /**
     * Update the specified prompt
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function promptUpdate($id)
    {   
        $prompt = ImagePrompt::where('id', $id)->first();

        $prompt->update([
            'title' => request('title'),
            'prompt' => request('prompt'),
        ]);

        toastr()->success(__('Image prompt has been successfully updated'));
        return redirect()->route('admin.davinci.image.prompt');     
    }


    /**
     * Enable the specified prompt.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function promptActivate(Request $request)
    {
        if ($request->ajax()) {

            $prompt = ImagePrompt::where('id', request('id'))->firstOrFail();  

            if ($prompt->status == true) {
                return  response()->json('active');
            }

            $prompt->update(['status' => true]);

            return  response()->json('success');
        }
    }


    /**
     * Disable the specified prompt.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function promptDeactivate(Request $request)
    {
        if ($request->ajax()) {

            $prompt = ImagePrompt::where('id', request('id'))->firstOrFail();  

            if ($prompt->status == false) {
                return  response()->json('deactive');
            }

            $prompt->update(['status' => false]);

            return  response()->json('success');
        }    
    }


    /**
     * Delete prompt
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function promptDelete(Request $request)
    {   
        if ($request->ajax()) {

            $name = ImagePrompt::find(request('id'));

            if($name) {

                $name->delete();

                return response()->json('success');

            } else{
                return response()->json('error');
            } 
        } 
    }
}
