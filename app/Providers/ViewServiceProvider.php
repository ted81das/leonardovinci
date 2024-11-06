<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use App\Services\HelperService;
use App\Models\MainSetting;
use App\Models\FrontendSection;
use App\Models\FrontendSetting;
use App\Models\SeoSetting;

class ViewServiceProvider extends ServiceProvider
{

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->shareSetting();
    }


    public function shareSetting(): void
    {
        $checkDBStatus = HelperService::checkDBStatus();

        if ($checkDBStatus) {
            if (Schema::hasTable('main_settings')) {
                $settings = MainSetting::first();
                View::share('settings', $settings);
            }

            if (Schema::hasTable('frontend_sections')) {
                $frontend_sections = FrontendSection::first();
                View::share('frontend_sections', $frontend_sections);
            }

            if (Schema::hasTable('frontend_settings')) {
                $frontend_settings = FrontendSetting::first();
                View::share('frontend_settings', $frontend_settings);
            }

            if (Schema::hasTable('seo_settings')) {
                $metadata = SeoSetting::first();
                View::share('metadata', $metadata);
            }

        } else {
            $frontend_settings = false;
        }

       
    }

 
}
