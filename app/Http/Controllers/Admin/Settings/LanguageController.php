<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use App\Models\MainSetting;


class LanguageController extends Controller
{   
    public function regenerate()
    {
        Artisan::call('elseyyid:location:install');

        return redirect()->route('elseyyid.translations.home')->with(config('elseyyid-location.message_flash_variable'), __('Language files regenerated!'));
    }

    public function setLocale(Request $request)
    {
        $settings = MainSetting::first();
        $settings->languages_default = $request->setLocale;
        $settings->save();
        LaravelLocalization::setLocale($request->setLocale);

        return redirect()->route('elseyyid.translations.home', [$request->setLocale])->with(config('elseyyid-location.message_flash_variable'), $request->setLocale);
    }

    public function languagesUpdateAll(Request $request)
    {
        $json = json_decode($request->data, true);
        $column_name = $request->lang;
        foreach ($json as $code => $column_value) {
            $code++;
            if (! empty($column_value)) {
                $test = \Elseyyid\LaravelJsonLocationsManager\Models\Strings::where('code', '=', $code)->update([$column_name => $column_value]);
            }
        }
        $lang = $column_name;
        $list = \Elseyyid\LaravelJsonLocationsManager\Models\Strings::pluck($lang, 'en');

        $new_json = json_encode_prettify($list);
        $filesystem = new \Illuminate\Filesystem\Filesystem;
        $filesystem->put(base_path('lang/'.$lang.'.json'), $new_json);

        if ($column_name == 'edit') {
            $enJsonPath = base_path('lang/en.json');
            $existingJson = $filesystem->get($enJsonPath);
            $existingValues = json_decode($existingJson, true);
            $editJsonPath = base_path('lang/edit.json');
            $editJson = $filesystem->get($editJsonPath);
            $editValues = json_decode($editJson, true);

            foreach ($editValues as $key => $column_value) {
                if (!empty($column_value)) {
                    $existingValues[$key] = $column_value;
                }
            }

            $updatedJson = json_encode_prettify($existingValues);
            $filesystem->put($enJsonPath, $updatedJson);
        }

        return response()->json(['code' => 200], 200);
    }


    public function languageSave(Request $request)
    {
        $settings = MainSetting::first();
        $codes = explode(',', $settings->languages);

        if ($request->state) {
            if (! in_array($request->lang, $codes)) {
                $codes[] = $request->lang;
            }
        } else {
            if (in_array($request->lang, $codes)) {
                unset($codes[array_search($request->lang, $codes)]);
            }
        }
        $settings->languages = implode(',', $codes);
        $settings->save();

        return response()->json(['code' => 200], 200);
    }

}
