<?php

namespace App\Http\Controllers\Admin\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FrontendPage;
use DataTables;
use Exception;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = FrontendPage::all()->sortByDesc("updated_at");
            return Datatables::of($data)
                    ->addIndexColumn()
                    ->addColumn('actions', function($row){
                        $actionBtn = '<div>                                            
                                            <a href="'. route("admin.settings.page.edit", $row["id"] ). '"><i class="fa-solid fa-pencil-square table-action-buttons edit-action-button" title="'. __('Edit Page') .'"></i></a>
                                            <a class="deletePageButton" id="'. $row["id"] .'" href="#"><i class="fa-solid fa-trash-xmark table-action-buttons delete-action-button" title="'. __('Delete Page') .'"></i></a>
                                        </div>';
                        return $actionBtn;
                    })
                    ->addColumn('custom-updated', function($row){
                        $created_on = '<span>'.date_format($row["updated_at"], 'M d, Y').'</span>';
                        return $created_on;
                    })
                    ->addColumn('custom-status', function($row){
                        $status = ($row['status']) ? '<span class="cell-box faq-visible">'.__('Active').'</span>': '<span class="cell-box faq-hidden">'.__('Deactive').'</span>';
                        return $status;
                    })
                    ->rawColumns(['actions', 'custom-status', 'custom-updated'])
                    ->make(true);
                    
        }

        return view('admin.frontend.page.index');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.frontend.page.create');
    }


    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(FrontendPage $id)
    {
        if ($id) {
            return view('admin.frontend.page.edit', compact('id'));
        }
        
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $status = (request('status') == 'on') ? true : false; 
        $main_nav = (request('top') == 'on') ? true : false; 
        $footer_nav = (request('footer') == 'on') ? true : false; 


        try {
            $page = new FrontendPage([
                'title' => request('title'),
                'content' => request('tinymce-editor'),
                'slug' => request('slug'),
                'status' => $status,
                'show_main_nav' => $main_nav,
                'show_footer_nav' => $footer_nav,
                'custom' => true,
                'seo_title' => request('seo_title'),
                'seo_url' => request('seo_url'),
                'seo_description' => request('seo_description'),
                'seo_keywords' => request('seo_keywords'),
            ]); 
                   
            $page->save();            
    
            toastr()->success(__('New page has been created successfully'));
            return redirect()->back();

        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return redirect()->back();
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FrontendPage $id)
    {
        $status = (request('status') == 'on') ? true : false; 
        $main_nav = (request('top') == 'on') ? true : false; 
        $footer_nav = (request('footer') == 'on') ? true : false; 

        try {
            $id->update([
                'title' => request('title'),
                'content' => request('tinymce-editor'),
                'slug' => request('slug'),
                'status' => $status,
                'show_main_nav' => $main_nav,
                'show_footer_nav' => $footer_nav,
                'custom' => true,
                'seo_title' => request('seo_title'),
                'seo_url' => request('seo_url'),
                'seo_description' => request('seo_description'),
                'seo_keywords' => request('seo_keywords'),
            ]);           
    
            toastr()->success(__('New page has been created successfully'));
            return redirect()->back();

        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return redirect()->back();
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        if ($request->ajax()) {

            $page = FrontendPage::find(request('id'));

            if($page) {

                $page->delete();

                return response()->json('success');

            } else{
                return response()->json('error');
            } 
        }
    }

}
