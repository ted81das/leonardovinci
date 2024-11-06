<?php

namespace App\Http\Controllers\Admin\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SeoSetting;
use Exception;

class SEOManagerController extends Controller
{
    /**
     * Show appearance settings page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.frontend.seo.index');
    }


    /**
     * Store appearance inputs properly in database and local storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $section = SeoSetting::first();
            $section->update([
                'home_description' => request('home_description'),
                'home_keywords' => request('home_keywords'),
                'home_author' => request('home_author'),
                'home_title' => request('home_title'),
                'home_url' => request('home_url'),
                'login_description' => request('login_description'),
                'login_keywords' => request('login_keywords'),
                'login_author' => request('login_author'),
                'login_title' => request('login_title'),
                'login_url' => request('login_url'),
                'register_description' => request('register_description'),
                'register_keywords' => request('register_keywords'),
                'register_author' => request('register_author'),
                'register_title' => request('register_title'),
                'register_url' => request('register_url'),
                'dashboard_description' => request('dashboard_description'),
                'dashboard_keywords' => request('dashboard_keywords'),
                'dashboard_author' => request('dashboard_author'),
            ]);            
    
            toastr()->success(__('SEO settings were saved successfully'));
            return redirect()->back();

        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return redirect()->back();
        }

    }

}
