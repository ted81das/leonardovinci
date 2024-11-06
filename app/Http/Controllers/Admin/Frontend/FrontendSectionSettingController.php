<?php

namespace App\Http\Controllers\Admin\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FrontendSection;
use Exception;

class FrontendSectionSettingController extends Controller
{
    /**
     * Show appearance settings page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.frontend.section.setting.index');
    }


    /**
     * Store appearance inputs properly in database and local storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $how_it_works = (request('how_it_works') == 'on') ? true : false; 
        $tools = (request('tools') == 'on') ? true : false; 
        $templates = (request('templates') == 'on') ? true : false; 
        $features = (request('features') == 'on') ? true : false; 
        $pricing = (request('pricing') == 'on') ? true : false; 
        $reviews = (request('reviews') == 'on') ? true : false; 
        $faq = (request('faq') == 'on') ? true : false; 
        $blog = (request('blog') == 'on') ? true : false; 
        $info = (request('info') == 'on') ? true : false; 
        $images = (request('images') == 'on') ? true : false; 
        $clients = (request('clients') == 'on') ? true : false; 
        $contact = (request('contact') == 'on') ? true : false; 


        try {
            $section = FrontendSection::first();
            $section->update([
                'main_banner_pretitle' => request('main_banner_pretitle'),
                'main_banner_title' => request('main_banner_title'),
                'main_banner_carousel' => request('main_banner_carousel'),
                'main_banner_subtitle' => request('main_banner_subtitle'),
                'how_it_works_status' => $how_it_works,
                'how_it_works_title' => request('how_it_works_title'),
                'how_it_works_subtitle' => request('how_it_works_subtitle'),
                'how_it_works_description' => request('how_it_works_description'),
                'tools_status' => $tools,
                'tools_title' => request('tools_title'),
                'tools_subtitle' => request('tools_subtitle'),
                'tools_description' => request('tools_description'),
                'templates_status' => $templates,
                'templates_title' => request('templates_title'),
                'templates_subtitle' => request('templates_subtitle'),
                'templates_description' => request('templates_description'),
                'features_status' => $features,
                'features_title' => request('features_title'),
                'features_subtitle' => request('features_subtitle'),
                'features_description' => request('features_description'),
                'pricing_status' => $pricing,
                'pricing_title' => request('pricing_title'),
                'pricing_subtitle' => request('pricing_subtitle'),
                'pricing_description' => request('pricing_description'),
                'reviews_status' => $reviews,
                'reviews_title' => request('reviews_title'),
                'reviews_subtitle' => request('reviews_subtitle'),
                'reviews_description' => request('reviews_description'),
                'faq_status' => $faq,
                'faq_title' => request('faq_title'),
                'faq_subtitle' => request('faq_subtitle'),
                'faq_description' => request('faq_description'),
                'blogs_status' => $blog,
                'blogs_title' => request('blog_title'),
                'blogs_subtitle' => request('blog_subtitle'),
                'blogs_description' => request('blog_description'),
                'images_status' => $images,
                'images_title' => request('images_title'),
                'images_subtitle' => request('images_subtitle'),
                'images_description' => request('images_description'),
                'info_status' => $info,
                'info_title' => request('info_title'),
                'info_description' => request('info_description'),
                'clients_status' => $clients,
                'clients_title' => request('clients_title'),
                'clients_title_dark' => request('clients_title_dark'),
                'contact_status' => $contact,
                'contact_location' => request('contact_location'),
                'contact_email' => request('contact_email'),
                'contact_phone' => request('contact_phone'),
            ]);            
    
            toastr()->success(__('Section settings were saved successfully'));
            return redirect()->back();

        } catch (Exception $e) {
            toastr()->error($e->getMessage());
            return redirect()->back();
        }

    }

}
