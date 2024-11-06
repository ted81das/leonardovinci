<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Extension;
use DataTables;

class ThemeController extends Controller
{
    private $extensions;

    public function __construct()
    {
        $this->extensions = new ExtensionController();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $themes = $this->extensions->themes();
        $extensions = Extension::get();

        return view('admin.themes.index', compact('themes', 'extensions'));
    }


    /**
     * Show Theme
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showTheme($slug)
    {   
        $theme = $this->extensions->search($slug);

        $extension = Extension::where('slug', $slug)->first();

        $tags = explode(',', $theme['tags']);

        return view('admin.themes.checkout', compact('theme', 'extension', 'tags'));     
    }


    /**
     * Show Theme
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function purchaseTheme(Request $request, $slug)
    {   
        session()->put('name', $slug);
        session()->put('type', $request->type);
        session()->put('amount', $request->value);

        return view('admin.themes.gateway');    
    }


    /**
     * Install Theme
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function installTheme(Request $request, $slug)
    {   
        if ($request->ajax()) {

            $response = $this->extensions->install($slug);                 
            return $response;  
        }
       
    }


    /**
     * Install Theme
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function activateTheme(Request $request, $slug)
    {   
        $theme = $this->extensions->checkPayment($slug);

        return redirect()->back();    
    }
    



}
