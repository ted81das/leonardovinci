<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Statistics\PaymentsService;
use App\Services\Statistics\CostsService;
use App\Services\Statistics\RegistrationService;
use App\Services\Statistics\UserRegistrationMonthlyService;
use App\Services\Statistics\DavinciUsageService;
use App\Services\Statistics\GoogleAnalyticsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\SupportTicket;
use App\Models\Payment;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('m'));

        $payment = new PaymentsService($year, $month);
        $cost = new CostsService($year, $month);
        $davinci = new DavinciUsageService($month, $year);
        $registration = new RegistrationService($year, $month);
        $user_registration = new UserRegistrationMonthlyService($month);
        $google = new GoogleAnalyticsService();
       
        $total_data_monthly = [
            'new_subscribers_current_month' => $registration->getNewSubscribersCurrentMonth(),
            'new_subscribers_past_month' => $registration->getNewSubscribersPastMonth(),
            'income_current_month' => $payment->getTotalPaymentsCurrentMonth(),
            'income_past_month' => $payment->getTotalPaymentsPastMonth(),
            'words_current_month' => $davinci->getTotalWordsCurrentMonth(),
            'words_past_month' => $davinci->getTotalWordsPastMonth(),
            'images_current_month' => $davinci->getTotalImagesCurrentMonth(),
            'images_past_month' => $davinci->getTotalImagesCurrentMonth(),
            'contents_current_month' => $davinci->getTotalContentsCurrentMonth(),
            'contents_past_month' => $davinci->getTotalContentsPastMonth(),
            'transactions_current_month' => $payment->getTotalTransactionsCurrentMonth(),
            'transactions_past_month' => $payment->getTotalTransactionsPastMonth(),
        ];

        $total_data_yearly = [
            'total_new_users' => $registration->getNewUsersCurrentYear(),
            'total_users' => $registration->getTotalUsers(),
            'total_subscribers' => $registration->getTotalSubscribers(),
            'total_nonsubscribers' => $registration->getTotalNonSubscribers(),
            'total_new_subscribers' => $registration->getNewSubscribersCurrentYear(),
            'total_income' => $payment->getTotalPaymentsCurrentYear(),
            'words_generated' => $davinci->getTotalWordsCurrentYear(),
            'images_generated' => $davinci->getTotalImagesCurrentYear(),
            'contents_generated' => $davinci->getTotalContentsCurrentYear(),
            'transactions_generated' => $payment->getTotalTransactionsCurrentYear(),
            'referral_earnings' => $payment->getTotalReferralEarnings(),
            'referral_payouts' => $payment->getTotalReferralPayouts(),
        ];
        
        $chart_data['total_new_users'] = json_encode($registration->getAllUsers());
        $chart_data['total_income'] = json_encode($payment->getPayments());
        $chart_data['monthly_earnings'] = json_encode($payment->getPayments());
        $chart_data['user_countries'] = json_encode($this->getAllCountries());

        $chart_data['gpt3_words'] = $davinci->gpt3TurboWords();
        $chart_data['gpt3_tasks'] = $davinci->gpt3TurboTasks();
        $chart_data['gpt4_words'] = $davinci->gpt4Words();
        $chart_data['gpt4_tasks'] = $davinci->gpt4Tasks();
        $chart_data['gpt4o_words'] = $davinci->gpt4oWords();
        $chart_data['gpt4o_tasks'] = $davinci->gpt4oTasks();
        $chart_data['gpt4t_words'] = $davinci->gpt4TurboWords();
        $chart_data['gpt4t_tasks'] = $davinci->gpt4TurboTasks();
        $chart_data['opus_words'] = $davinci->opusWords();
        $chart_data['opus_tasks'] = $davinci->opusTasks();
        $chart_data['sonnet_words'] = $davinci->sonnetWords();
        $chart_data['sonnet_tasks'] = $davinci->sonnetTasks();
        $chart_data['haiku_words'] = $davinci->haikuWords();
        $chart_data['haiku_tasks'] = $davinci->haikuTasks();
        $chart_data['gemini_words'] = $davinci->geminiWords();
        $chart_data['gemini_tasks'] = $davinci->geminiTasks();
        

        $percentage['subscribers_current'] = json_encode($registration->getNewSubscribersCurrentMonth());
        $percentage['subscribers_past'] = json_encode($registration->getNewSubscribersPastMonth());
        $percentage['income_current'] = json_encode($payment->getTotalPaymentsCurrentMonth());
        $percentage['income_past'] = json_encode($payment->getTotalPaymentsPastMonth());
        $percentage['images_current'] = json_encode($davinci->getTotalImagesCurrentMonth());
        $percentage['images_past'] = json_encode($davinci->getTotalImagesCurrentMonth());
        $percentage['contents_current'] = json_encode($davinci->getTotalContentsCurrentMonth());
        $percentage['contents_past'] = json_encode($davinci->getTotalContentsPastMonth());
        $percentage['transactions_current'] = json_encode($payment->getTotalTransactionsCurrentMonth());
        $percentage['transactions_past'] = json_encode($payment->getTotalTransactionsPastMonth());

        $notifications = Auth::user()->notifications->where('type', '<>', 'App\Notifications\GeneralNotification')->all();
        $tickets = SupportTicket::whereNot('status', 'Resolved')->whereNot('status', 'Closed')->latest()->paginate(8);

        $users = User::latest()->take(10)->get();
        $transaction = Payment::latest()->take(10)->get();  
 
        return view('admin.dashboard.index', compact('total_data_monthly', 'total_data_yearly', 'chart_data', 'percentage', 'users', 'transaction', 'notifications', 'tickets'));
    }


    /**
     * Display GA4 info
     *
     * @return \Illuminate\Http\Response
     */
    public function analytics(Request $request)
    {
        if ($request->ajax()) {

            $google = new GoogleAnalyticsService();

            if (!empty(config('services.google.analytics.property')) && !empty(config('services.google.analytics.credentials'))) {
                $data['traffic_label'] = json_encode($google->getTrafficLabels());
                $data['traffic_data'] = json_encode($google->getTrafficData());
                $data['google_average_session'] = $google->averageSessionDuration();
                $data['google_sessions'] = number_format($google->sessions());
                $data['google_session_views'] = number_format((float)$google->sessionViews(), 2);
                $data['google_bounce_rate'] = $google->bounceRate();
                $data['google_users'] = json_encode($google->users());
                $data['google_user_sessions'] = json_encode($google->userSessions());

                $data['google_countries'] = $this->getGACountries();
                $data['status'] = 200;
                return $data;
            }    
        }
    }


    /**
     * Show list of all countries
     */
    public function getAllCountries()
    {        
        $countries = User::select(DB::raw("count(id) as data, country"))
                ->groupBy('country')
                ->orderBy('data')
                ->pluck('data', 'country');    
        
        return $countries;        
    }


    /**
     * Show list of all countries
     */
    public function getGACountries()
    {        
        $google = new GoogleAnalyticsService();

        $countries= $google->userCountries();
        $total = $google->userCountriesTotal();
        $list = '';

        foreach ($countries as $data) {

            $flag = theme_url('img/flags/'.strtolower($data['countryId']).'.svg');
            $width = ($data['totalUsers']/$total)*100;
            $value = ($data['totalUsers']/$total)*100;
            $list .= '<li>
                        <div class="card-body pt-2 pb-2 pl-0 pr-0 d-flex">
                            <div class="dashboard-flags overflow-hidden"><img alt="User Avatar" class="rounded-circle" src="'.$flag.'"></div>
                                <div class="template-title mt-auto mb-auto d-flex justify-content-center">
                                <h6 class="fs-12 font-weight-semibold text-muted mb-0 ml-4 mt-auto mb-auto">' . __($data['country']) . '</h6>																										
                            </div>	
                            <div class="progress mt-auto mb-auto ml-4 text-right" style="height: 5px; width: 150px">
                                <div class="progress-bar" role="progressbar" style="width: ' . $width . '%;" aria-valuenow="'.$value.'" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="template-title mt-auto mb-auto justify-content-center">
                                <h6 class="fs-10 text-muted mb-0 ml-4 mt-auto mb-auto">'. $data['totalUsers'] . '</h6>																										
                            </div>						
                        </div>
                    </li>';
        }
            											
        return $list;        
    }

}
