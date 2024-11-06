<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use App\Models\Extension;
use App\Models\Setting;
use App\Models\MainSetting;
use ZanySoft\Zip\Zip;
use Exception;

class ExtensionController extends Controller
{
    public const API_URL = 'https://marketplace.berkine.net/api/';
    private $root_path;

    public function extensions()
    {
        return $this->get();
    }


    public function themes()
    {
        return $this->get(true);
    }


    public function search($slug)
    {
        $response = $this->request('post', "extension/{$slug}");

        if ($response->ok()) {

            $data = $response->json('data');

            $extension = Extension::where('slug', $slug)->first();

            return array_merge($data, [
                'latest_version' => $extension?->version,
                'installed' => (bool) $extension?->installed,
                'upgradable' => $extension?->version !== $data['version'],
            ]);
        }

        return [];
    }


    public function verify($slug, $payment)
    {
        $response = $this->request('post', "extension/purchase/{$slug}/verify/{$payment}");

        if ($response->ok()) {

            $status = $response->json('status');
            $data = $response->json('data');

            $extension = Extension::where('slug', $slug)->first();
            
            if ($status == 'succeeded') {
                $extension->purchased = true;
                $extension->save();

                return array_merge($data, [
                    'latest_version' => $extension?->version,
                    'installed' => (bool) $extension?->installed,
                    'upgradable' => $extension?->version !== $data['version'],
                    'purchased' => true
                ]);

            } else {
                return [];
            }
            
        }

        return [];
    }


    public function checkPayment($slug)
    {
        if ($slug != 'default') {
            $response = $this->request('post', "extension/purchase/check/{$slug}");

            if ($response->ok()) {

                $data = $response->json('data');

                $extension = Extension::where('slug', $slug)->first();
                $extension->purchased = true;
                $extension->save();

                $setting = MainSetting::first();

                if (strtolower($data['type']) == 'dashboard') {
                    $setting->dashboard_theme = $extension->slug;
                } elseif (strtolower($data['type']) == 'frontend') {
                    $setting->frontend_theme = $extension->slug;
                } else {
                    $setting->dashboard_theme = $extension->slug;
                    $setting->frontend_theme = $extension->slug;
                }

                $setting->save();
                
            }
        } else {
            $setting = MainSetting::first();

            $setting->dashboard_theme = 'default';
            $setting->frontend_theme = 'default';         
            $setting->save();
        }

        return [];
    }


    public function get(bool $is_theme = false)
    {
        $appVersion = env('APP_VERSION');

        $response = $this->request('post', 'extension', [
            'is_theme' => $is_theme,
            'app_version' => $appVersion
        ]);

        if ($response->ok()) {

            $data = $response->json('data');

            $this->update($data);

            $purchases = $this->request('post', 'extension/user/purchases');

            if ($purchases->ok()) {

                $purchase_data = $purchases->json('data');

                $this->updatePurchases($purchase_data);
            }

            return $this->merge($data);
        }

        return [];
    }


    public function install(string $slug)
    {
        $this->root_path = base_path();
        $appVersion = env('APP_VERSION');
        $setting = MainSetting::first();

        if ($slug == 'default') {
            
            $setting->dashboard_theme = $slug;
            $setting->frontend_theme = $slug;
            $setting->save();

            Artisan::call('optimize:clear');

            return [
                'status' => true,
                'message' => __('Theme installation completed successfully'),
            ];
        }

        $extension = $this->search($slug);

        $response = $this->request('post', 'extension/version/install', [
            'slug' => $slug,
            'app_version' => $appVersion
        ]);

        if ($response->ok()) {

            $zip_file = new Zip();

            $content = $response->body();

            $destination = "destination.zip"; 
            Storage::disk('root')->put($destination, $content);

            try {
                
                $is_valid = $zip_file->check($this->root_path . '/' . $destination);
    
            } catch(Exception $e) {
                \Log::info($e->getMessage());
                return [
                    'status' => false,
                    'message' => $e->getMessage(),
                ];
            }
    
            if($is_valid === TRUE) {
                $zip = $zip_file->open($this->root_path . '/' . $destination);
                $zip->extract($this->root_path . '/'); 		
                $zip->close();		
                Storage::disk('root')->delete($destination);
            }

            Extension::query()->where('slug', $slug)->update(['installed' => 1, 'version' => $extension['version']]);

            if (strtolower($extension['type']) == 'dashboard') {
                $setting->dashboard_theme = $extension['slug'];
            } elseif (strtolower($extension['type']) == 'frontend') {
                $setting->frontend_theme = $extension['slug'];
            } else {
                $setting->dashboard_theme = $extension['slug'];
                $setting->frontend_theme = $extension['slug'];
            }

            $setting->save();

            Artisan::call('optimize:clear');

            return [
                'status' => true,
                'message' => __('Theme installation completed successfully'),
            ];

        } else {
            return [
                'status' => false,
                'message' => __('There was an error in theme installation, please contact support team'),
            ];
        }
    }


    public function request(string $method, string $route, array $body = [], $url = null)
    {   
        $user_data = $this->userInfo();
        $url = $url ?? self::API_URL.$route;

        return Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'x-domain' => request()->getHost(),
            'x-username' => $user_data['username'],
            'x-activation-code' => $user_data['license'],
        ])->when($method === 'post', function ($http) use ($url, $body) {
            return $http->post($url, $body);
        }, function ($http) use ($url, $body) {
            return $http->get($url, $body);
        });
    }

    public function merge(array $data): array
    {
        $extensions = Extension::query()->get();

        return collect($data)->map(function ($extension) use ($extensions) {
            $value = $extensions->firstWhere('slug', $extension['slug']);

            return array_merge($extension, [
                'latest_version' => $value?->version,
                'installed' => (bool) $value?->installed,
                'upgradable' => $value?->version !== $extension['version'],
            ]);
        })->toArray();
    }

    private function update(array $data): void
    {
        foreach ($data as $extension) {
            Extension::query()->firstOrCreate([
                'slug' => $extension['slug'],
                'is_theme' => $extension['is_theme'],
            ], [
                'version' => $extension['version'],
                'is_free' => $extension['is_free'],
            ]);
        }
    }

    private function updatePurchases(array $data): void
    {
        
        foreach ($data as $extension) {
            if ($extension['status'] == 'succeeded') {
                $ext = Extension::where('slug', $extension['slug'])->first();
                if($ext) {
                    $ext->purchased = true;
                    $ext->save();
                }
            }
            
        }
    }


    public function userInfo()
    {
        $information_rows = ['license', 'username'];
        $information = [];
        $settings = Setting::all();

        foreach ($settings as $row) {
            if (in_array($row['name'], $information_rows)) {
                $information[$row['name']] = $row['value'];
            }
        }

        return $information;
    }


    public function checkDownloadLicense($license)
    {
        $response = $this->request('post', 'license/check', [
            'license' => $license,
        ]);

        if ($response->ok()) {

            $data = $response->json('data');

            return $data;
        } else {
            return false;
        }
    }


    public function sak()
    {
        $response = $this->request('post', "extension/sak");

        if ($response->ok()) {

            $data = $response->json('data');

            return $data;
        }

    }


}