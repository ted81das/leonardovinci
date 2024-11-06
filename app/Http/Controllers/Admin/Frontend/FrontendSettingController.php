<?php

namespace App\Http\Controllers\Admin\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FrontendSetting;

class FrontendSettingController extends Controller
{
    /**
     * Show appearance settings page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.frontend.frontend.index');
    }


    /**
     * Store appearance inputs properly in database and local storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate([
            'enable-redirection' => 'sometimes|required',
            'url' => 'required_if:enable-redirection,on',
        ]);

        $setting = FrontendSetting::first();

        if ($setting) {
            $setting->update([
                'custom_css_url' => request('css'),
                'custom_js_url' => request('js'),
                'custom_header_code' => $request->header_code,
                'custom_body_code' => $request->body_code,
            ]);   
        } else {
            $custom = new FrontendSetting([
                'custom_css_url' => request('css'),
                'custom_js_url' => request('js'),
                'custom_header_code' => $request->header_code,
                'custom_body_code' => $request->body_code,
            ]); 
                   
            $custom->save();  
        }

        $this->storeSettings('FRONTEND_MAINTENANCE_MODE', request('maintenance'));
        $this->storeSettings('FRONTEND_FRONTEND_PAGE', request('frontend'));

        $this->storeSettings('FRONTEND_CUSTOM_URL_STATUS', request('enable-redirection'));
        $this->storeSettings('FRONTEND_CUSTOM_URL_LINK', request('url'));

        $this->storeSettings('FRONTEND_SOCIAL_TWITTER', request('twitter'));
        $this->storeSettings('FRONTEND_SOCIAL_FACEBOOK', request('facebook'));
        $this->storeSettings('FRONTEND_SOCIAL_LINKEDIN', request('linkedin'));
        $this->storeSettings('FRONTEND_SOCIAL_INSTAGRAM', request('instagram'));
        $this->storeSettings('FRONTEND_SOCIAL_YOUTUBE', request('youtube'));

        $data['status'] = 200;                 
        return $data; 
    }


    /**
     * Record in .env file
     */
    private function storeSettings($key, $value)
    {
        $path = base_path('.env');

        if (file_exists($path)) {

            file_put_contents($path, str_replace(
                $key . '=' . env($key), $key . '=' . $value, file_get_contents($path)
            ));

        }
    }
}
