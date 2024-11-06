<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use App\Services\HelperService;
use App\Models\MainSetting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        app()->useLangPath(
            base_path('lang')
        );

        $locale = 'en';

        $checkDBStatus = HelperService::checkDBStatus();

        if ($checkDBStatus) {
            
            if (Schema::hasTable('main_settings')) {
                $settings = MainSetting::first();
                $locale = HelperService::checkField('default_language',$locale);
                
                if (HelperService::checkField('frontend_theme') == null) {
                    $settings->frontend_theme = 'default';
                    $settings->save();
                }
    
                if (HelperService::checkField('dashboard_theme') == null) {
                    $settings->dashboard_theme = 'default';
                    $settings->save();
                }
    
                $frontend_theme = $settings->frontend_theme;
                $dashboard_theme = $settings->dashboard_theme;
    
                if ($frontend_theme == $dashboard_theme) {
                    \Theme::set($frontend_theme);
                } else {
                    if (request()->is('app*') || request()->is('*/app*')) {
                        \Theme::set($dashboard_theme);
                    } else {
                        \Theme::set($frontend_theme);
                    }
                }
            }

        } else {
            \Theme::set('default');
        }
        

        app()->setLocale($locale);

        // URL::forceRootUrl(config('app.url'));
        // if (str_contains(config('app.url'), 'https://')) {
        //     URL::forceScheme('https');
        // }
    }
}
