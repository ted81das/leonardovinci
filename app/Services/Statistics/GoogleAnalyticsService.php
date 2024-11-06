<?php

namespace App\Services\Statistics;

use AkkiIo\LaravelGoogleAnalytics\Facades\LaravelGoogleAnalytics;
use AkkiIo\LaravelGoogleAnalytics\Period;
use Google\Analytics\Data\V1beta\Filter\StringFilter\MatchType;
use Google\Analytics\Data\V1beta\MetricAggregation;
use Google\Analytics\Data\V1beta\Filter\NumericFilter\Operation;
use Illuminate\Support\Facades\Auth;
use App\Models\Content;
use App\Models\Image;
use App\Models\VoiceoverResult;
use App\Models\Transcript;
use App\Models\Code;
use App\Models\User;
use DB;

class GoogleAnalyticsService 
{
    private $month;
    private $year;

    public function __construct(int $month = null, int $year = null)
    {
        $this->month = $month;
        $this->year = $year;
    }


    public function users()
    {
        $analyticsData = LaravelGoogleAnalytics::getTotalUsersByDate(Period::days(30));

        $days = [];
        $users = [];

        foreach ($analyticsData as $key=>$value) {
            $date = date("M d", strtotime($value['date']));
            $days[$key] = $date;
            $users[$key] = $value['totalUsers'];
                        
        }
        
        $data['days'] = $days;
        $data['users'] = $users;
        return $data;
    }


    public function userSessions()
    {
        $analyticsData = LaravelGoogleAnalytics::dateRanges(Period::days(30))
        ->metrics('sessions')
        ->dimensions('date')
        ->orderByDimension('date')
        ->get();

        $raw = $analyticsData->table;

        $days = [];
        $sessions = [];

        foreach ($raw as $key=>$value) {
            $date = date("M d", strtotime($value['date']));
            $days[$key] = $date;
            $sessions[$key] = $value['sessions'];
                        
        }
        
        $data['days'] = $days;
        $data['sessions'] = $sessions;
        return $data;
    }


    public function userCountries()
    {
        $analyticsData = LaravelGoogleAnalytics::getMostUsersByCountry(Period::days(180), 20);
        
        return $analyticsData;
    }


    public function userCountriesTotal()
    {
        $analyticsData = LaravelGoogleAnalytics::getMostUsersByCountry(Period::days(180), 20);
        $total = 0;

        foreach ($analyticsData as $key=>$value) {
            $total += $value['totalUsers'];         
        }
        
        return $total;
    }


    public function averageSessionDuration()
    {
        $analyticsData = LaravelGoogleAnalytics::getAverageSessionDuration(Period::days(30));

        $seconds = round($analyticsData);
        $time = sprintf('%02dh %02dm %02ds', ($seconds/ 3600),($seconds/ 60 % 60), $seconds% 60);
        
        return $time;
    }


    public function totalViews()
    {
        $analyticsData = LaravelGoogleAnalytics::getTotalViews(Period::days(30));
        
        return $analyticsData;
    }


    public function bounceRate()
    {
        $analyticsData = LaravelGoogleAnalytics::dateRanges(Period::days(30))
        ->metrics('bounceRate')
        ->get();

        $data = $analyticsData->table[0]['bounceRate'];
        
        return $data;
    }


    public function sessions()
    {
        $analyticsData = LaravelGoogleAnalytics::dateRanges(Period::days(30))
        ->metrics('sessions')
        ->get();

        $data = $analyticsData->table[0]['sessions'];
        
        return $data;
    }


    public function sessionViews()
    {
        $analyticsData = LaravelGoogleAnalytics::dateRanges(Period::days(30))
        ->metrics('screenPageViewsPerSession')
        ->get();

        $data = $analyticsData->table[0]['screenPageViewsPerSession'];
        
        return $data;
    }


    public function userBrowser($user = null)
    {
        $analyticsData = LaravelGoogleAnalytics::getTotalUsersBySessionSource(Period::days(180) );

        // $analyticsData = LaravelGoogleAnalytics::dateRanges(Period::days(30), Period::days(60))
        // ->metrics('active1DayUsers', 'active7DayUsers')
        // ->dimensions('browser', 'language')
        // ->metricAggregations(MetricAggregation::TOTAL, MetricAggregation::MINIMUM)
        // ->whereDimension('browser', MatchType::CONTAINS, 'firefox')
        // ->whereMetric('active7DayUsers', Operation::GREATER_THAN, 50)
        // ->orderByDimensionDesc('language')
        // ->get();; 
        
        return $analyticsData;
    }

    public function getTrafficLabels()
    {
        $data = LaravelGoogleAnalytics::getTotalUsersBySessionSource(Period::days(180) );
        $values = [];

        foreach ($data as $key=>$value) {
            if ($key < 6) {
                $values[$key] = $value['sessionSource'];
            }            
        }
        
        return $values;
    }


    public function getTrafficData()
    {
        $data = LaravelGoogleAnalytics::getTotalUsersBySessionSource(Period::days(180) );
        $values = [];

        foreach ($data as $key=>$value) {
            if ($key < 6) {
                $values[$key] = $value['totalUsers'];
            }            
        }
        
        return $values;
    }


    
}