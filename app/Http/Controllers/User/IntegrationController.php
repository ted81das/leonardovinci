<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\LicenseController;
use Illuminate\Http\Request;
use App\Models\UserIntegration;
use App\Models\Integration;
use App\Services\HelperService;
use Exception;

class IntegrationController extends Controller
{
    private $api;

    public function __construct()
    {
        $this->api = new LicenseController();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $verify = $this->api->verify_license();
        $type = (isset($verify['type'])) ? $verify['type'] : '';

        $integrations = Integration::get();

        $wordpress_setup = UserIntegration::where('integration_id', 1)->where('user_id', auth()->user()->id)->first();
        $wordpress = ($wordpress_setup) ? $wordpress_setup->status : null;

        return view('user.integration.index', compact('type', 'integrations', 'wordpress'));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Integration $id)
    {
        $fields = json_decode($id->fields, true);

        $current = UserIntegration::where('integration_id', $id->id)->where('user_id', auth()->user()->id)->first();
        if ($current) {
            $credentials = json_decode($current->credentials, true);
        } else {
            $credentials = null;
        }

        return view('user.integration.edit', compact('id', 'fields', 'credentials', 'current'));
    }


     /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Integration $id)
    {   
        $credentials = [];
        $token = '';
        $status = (request('status') == 'on') ? true : false;
        $fields = json_decode($id->fields);

        if ($status) {
            if ($id->id == 1) {
                $verify = HelperService::checkWordpress($request->domain, $request->username, $request->password);
                if(isset($verify['status'])) {
                    if ($verify['status'] == 'error') {
                        toastr()->error(__($verify['error_description']));
                        return redirect()->back();
                    }
                }
                
                if(isset($verify['jwt_token'])) {
                    $token = $verify['jwt_token'];
                } else {
                    toastr()->error(__('Incorrect credentials provided, please recheck them'));
                    return redirect()->back();
                }
                
            } 
        }

        foreach ($fields as $field) {
            $credentials[$field->name] = request($field->name);
        }

        if ($status) {
            if ($id->id ==1 ) {
                $credentials['jwt_token'] = $token;
            }
        }  

        $credentials = json_encode($credentials);

        $current = UserIntegration::where('integration_id', $id->id)->where('user_id', auth()->user()->id)->first();
        
        if ($current) {

            $current->update([
                'credentials' => $credentials,
                'status' => $status
            ]); 

            toastr()->success(__('Integration credentials were successfully updated'));
            return redirect()->route('user.integration');

        } else {

            $integration = new UserIntegration([
                'user_id' => auth()->user()->id,
                'integration_id' => $id->id,
                'credentials' => $credentials,
                'status' => $status
            ]); 
            
            $integration->save(); 

            toastr()->success(__('Integration credentials were successfully stored'));
            return redirect()->route('user.integration');
        }

    }

}
