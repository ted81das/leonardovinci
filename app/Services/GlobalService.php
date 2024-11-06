<?php

function languagesList2() {
    $fields = \DB::getSchemaBuilder()->getColumnListing('strings');
    $exceptions = ['en','code','created_at','updated_at'];
    $filtered = collect($fields)->filter(function ($value, $key) use($exceptions){
        if (!in_array($value,$exceptions) ) {
            return $value;
        }
    });
    return $filtered->all();
}

function custom_theme_url($url) {

        \Log::info($url);
        \Log::info(theme_url($url));
        return theme_url($url);

}


function get_theme() {
    $theme = \Theme::get();
    if(!$theme){
        $theme = 'default';
    }
    return $theme;
}
