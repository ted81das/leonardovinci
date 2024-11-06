<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Mail\ContactFormAdmin;
use App\Mail\ContactFormUser;
use App\Models\SubscriptionPlan;
use App\Models\PrepaidPlan;
use App\Models\CustomTemplate;
use App\Models\Template;
use App\Models\Blog;
use App\Models\Review;
use App\Models\FrontendPage;
use App\Models\Faq;
use App\Models\Category;
use App\Models\FrontendStep;
use App\Models\FrontendTool;
use App\Models\FrontendFeature;
use App\Models\FrontendClient;
use App\Models\FrontendCase;
use App\Models\User;
use App\Models\MainSetting;

class HomeController extends Controller
{
    /**
     * Show home page
     */
    public function index()
    {

        $review_exists = Review::count();   
        $review_second_exists = Review::where('row', 'second')->count();   
        $reviews = Review::all();

        $monthly = SubscriptionPlan::where('status', 'active')->where('payment_frequency', 'monthly')->count();
        $yearly = SubscriptionPlan::where('status', 'active')->where('payment_frequency', 'yearly')->count();
        $lifetime = SubscriptionPlan::where('status', 'active')->where('payment_frequency', 'lifetime')->count();
        $prepaid = PrepaidPlan::where('status', 'active')->count();

        $monthly_subscriptions = SubscriptionPlan::where('status', 'active')->where('payment_frequency', 'monthly')->get();
        $yearly_subscriptions = SubscriptionPlan::where('status', 'active')->where('payment_frequency', 'yearly')->get();
        $lifetime_subscriptions = SubscriptionPlan::where('status', 'active')->where('payment_frequency', 'lifetime')->get();
        $prepaids = PrepaidPlan::where('status', 'active')->get();

        $other_templates = Template::where('status', true)->orderBy('group', 'desc')->get();   
        $custom_templates = CustomTemplate::where('status', true)->where('type', '<>', 'private')->orderBy('group', 'desc')->get();   
        
        $check_categories = Template::where('status', true)->groupBy('group')->pluck('group')->toArray();
        $check_custom_categories = CustomTemplate::where('status', true)->where('type', '<>', 'private')->groupBy('group')->pluck('group')->toArray();
        $active_categories = array_unique(array_merge($check_categories, $check_custom_categories));
        $categories = Category::whereIn('code', $active_categories)->orderBy('name', 'asc')->get(); 

        $steps = FrontendStep::orderBy('order', 'asc')->get();
        $tools = FrontendTool::where('status', true)->get();
        $cases = FrontendCase::where('status', true)->get();

        $faq_exists = Faq::count();        
        $faqs = Faq::where('status', 'visible')->get();

        $client_exists = FrontendClient::count();        
        $clients = FrontendClient::all();

        $blog_exists = Blog::count();
        $blogs = Blog::where('status', 'published')->orderBy('created_at', 'desc')->get();

        $custom_pages = FrontendPage::get();

        $features = FrontendFeature::where('status', true)->get();
        $settings = MainSetting::first();

        return view('home', compact('cases', 'client_exists', 'clients', 'custom_pages', 'steps', 'tools', 'features', 'settings', 'blog_exists', 'blogs', 'faq_exists', 'faqs', 'review_exists', 'review_second_exists', 'reviews', 'monthly', 'yearly', 'monthly_subscriptions', 'yearly_subscriptions', 'prepaids', 'prepaid', 'other_templates', 'custom_templates', 'lifetime', 'lifetime_subscriptions', 'categories'));
    }



    /**
     * Frontend show blog
     * 
     */
    public function blogShow($slug)
    {
        $blog = Blog::where('url', $slug)->firstOrFail();
        $custom_pages = FrontendPage::get();

        return view('frontend.blogs.show', compact('custom_pages', 'blog'));
    }


    /**
     * Frontend show contact
     * 
     */
    public function contactShow()
    {
        $custom_pages = FrontendPage::get();

        return view('frontend.contact.index', compact('custom_pages'));
    }



    /**
     * Frontend contact us form record
     * 
     */
    public function contactSend(Request $request)
    {
        request()->validate([
            'name' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required',
            'message' => 'required',
        ]);

        if (config('services.google.recaptcha.enable') == 'on') {

            $recaptchaResult = $this->reCaptchaCheck(request('recaptcha'));

            if ($recaptchaResult->success != true) {
                toastr()->error(__('Google reCaptcha Validation has Failed'));
                return redirect()->back();
            }

            if ($recaptchaResult->score >= 0.3) {

                try {

                    Mail::to(config('mail.from.address'))->send(new ContactFormAdmin($request));
                    Mail::to($request->email)->send(new ContactFormUser($request));
 
                    if (Mail::flushMacros()) {
                        toastr()->error(__('Sending email failed, please try again.'));
                        return redirect()->back();
                    }
                    
                } catch (\Exception $e) {
                    toastr()->error(__('Sending email failed, please contact support team.'));
                    return redirect()->back();
                }

                toastr()->success(__('Email was successfully sent'));
                return redirect()->back();

            } else {
                toastr()->error(__('Google reCaptcha Validation has Failed'));
                return redirect()->back();
            }
        
        } else {

            try {

                Mail::to(config('mail.from.address'))->send(new ContactFormAdmin($request));
                Mail::to($request->email)->send(new ContactFormUser($request));
 
                if (Mail::flushMacros()) {
                    toastr()->error(__('Sending email failed, please try again.'));
                    return redirect()->back();
                }

            } catch (\Exception $e) {
                toastr()->error(__('Sending email failed, please contact support team.'));
                return redirect()->back();
            }

            toastr()->success(__('Email was successfully sent'));
            return redirect()->back();
        }  
    }


    /**
     * Verify reCaptch for frontend contact us page (if enabled)
     * 
     */
    private function reCaptchaCheck($recaptcha)
    {
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $remoteip = $_SERVER['REMOTE_ADDR'];

        $data = [
                'secret' => config('services.google.recaptcha.secret_key'),
                'response' => $recaptcha,
                'remoteip' => $remoteip
        ];

        $options = [
                'http' => [
                'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
                ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $resultJson = json_decode($result);

        return $resultJson;
    }


    public function showUnsubscribe(Request $request)
    {
        $email = $request->email;
        return view('auth.unsubscribe', compact('email'));
    }


    public function unsubscribe($email)
    {
        $user = User::where('email', $email)->first();
        
        if ($user) {
            $user->email_opt_in = false;
            $user->save();

            toastr()->success(__('You have successfully unsubscribed from our newsletters'));
            return redirect()->back();
        } else {
            toastr()->warning(__('You are not subscribed to any newsletters'));
            return redirect()->back();
        }
    }


    public function showPage($slug){

        $page = FrontendPage::where('slug', $slug)->first();
        $custom_pages = FrontendPage::get();

        if ( ! $page->status ) {
            abort(404);
        }

        if ($page) {
            return view('frontend.page.index', compact('page', 'custom_pages'));
        } else {
            abort(404);
        }
    }

}
