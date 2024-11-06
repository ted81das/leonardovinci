<?php

namespace App\Http\Controllers\Admin\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\MainSetting;

class AppearanceController extends Controller
{
    /**
     * Show appearance settings page
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.frontend.appearance.index');
    }


    /**
     * Store appearance inputs properly in database and local storage
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $logos = [
            'logo_frontend' => 'frontend',
            'logo_frontend_collapsed' => 'frontend-collapsed',
            'logo_frontend_footer' => 'frontend-footer',
            'logo_dashboard' => 'dashboard',
            'logo_dashboard_dark' => 'dashboard-dark',
            'logo_dashboard_collapsed' => 'dashboard-collapsed',
            'logo_dashboard_collapsed_dark' => 'dashboard-collapsed-dark',
        ];

        foreach ($logos as $logo => $prefix) {

            if ($request->hasFile($logo)) {
                $path = 'uploads/logo/';
                $image = $request->file($logo);
                $image_name = $prefix.'-logo.'.$image->getClientOriginalExtension();

                //Resim uzantı kontrolü
                $imageTypes = ['jpg', 'jpeg', 'png', 'svg', 'webp'];
                if (! in_array(Str::lower($image->getClientOriginalExtension()), $imageTypes)) {
                    $data = [
                        'errors' => ['The file extension must be jpg, jpeg, png, webp or svg.'],
                    ];

                    return response()->json($data, 419);
                }

                $image->move($path, $image_name);

                $settings = MainSetting::first();
                $settings->{$logo} = $path.$image_name;
                $settings->save();
            }

        }


        if (request()->has('favicon_logo')) {
            
            try {
                request()->validate([
                    'favicon_logo' => 'nullable|mimes:ico'
                ]);

            } catch (\Exception $e) {
                toastr()->error(__('Incorrect image format or file size, favicon image must be in ICO format'));
                return redirect()->back();
            }
            
            $image = request()->file('favicon_logo');
            $name = 'favicon';         
            $folder = 'uploads/logo/';
            
            $this->uploadImage($image, $folder, 'public', $name);
        }

        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('view:clear');

        toastr()->success(__('Logos were successfully updated'));
        return redirect()->back();
    }


    /**
     * Upload logo images
     */
    public function uploadImage(UploadedFile $file, $folder = null, $disk = 'public', $filename = null)
    {
        $name = !is_null($filename) ? $filename : Str::random(5);

        $file->storeAs($folder, $name .'.'. $file->getClientOriginalExtension(), $disk);

    }

}
