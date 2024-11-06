<?php

namespace App\Services\Statistics;

use App\Models\Payment;
use App\Models\Payout;
use App\Models\Referral;
use DB;

class PaymentsService 
{
    private $year;
    private $month;

    public function __construct(int $year, int $month) 
    {
        $this->year = $year;
        $this->month = $month;
    }


    public function getPayments()
    {
        $payments = Payment::select(DB::raw("sum(price) as data"), DB::raw("MONTH(created_at) month"))
                ->whereYear('created_at', $this->year)
                ->where('status', 'completed')
                ->groupBy('month')
                ->orderBy('month')
                ->get()->toArray();  
        
        $data = [];

        for($i = 1; $i <= 12; $i++) {
            $data[$i] = 0;
        }

        foreach ($payments as $row) {				            
            $month = $row['month'];
            $data[$month] = intval($row['data']);
        }
        
        return $data;
    }


    public function getSourceRevenue() {
        $payments = Payment::select(DB::raw("sum(price) as data, plan_name"))
                ->where('status', 'completed')
                ->groupBy('plan_name')
                ->orderBy('data')
                ->get()->toArray();  

        $data = [];
        foreach ($payments as $row) {				            
            $data[$row['plan_name']] = $row['data'];
        }

        return $data;
    }
    

    public function getTotalPayments()
    {   
        $payments = Payment::select(DB::raw("sum(price) as data"))                
                ->where('status', 'completed')
                ->get();  
        
        return $payments;
    }


    public function getTotalPaymentsCurrentYear()
    {   
        $payments = Payment::select(DB::raw("sum(price) as data"))                
                ->whereYear('created_at', $this->year)
                ->where('status', 'completed')
                ->get();  
        
        return $payments;
    }


    public function getTotalPaymentsCurrentMonth()
    {   
        $payments = Payment::select(DB::raw("sum(price) as data"))
                ->whereMonth('created_at', $this->month)
                ->whereYear('created_at', $this->year)
                ->where('status', 'completed')
                ->get();  
        
        return $payments;
    }


    public function getTotalPaymentsPastMonth()
    {   
        $date = \Carbon\Carbon::now();
        $pastMonth =  $date->subMonth()->format('m');

        $payments = Payment::select(DB::raw("sum(price) as data"))
                ->whereMonth('created_at', $pastMonth)
                ->whereYear('created_at', $this->year)
                ->where('status', 'completed')
                ->get();  
        
        return $payments;
    }


    public function getTotalTransactionsCurrentMonth()
    {   
        $payments = Payment::select(DB::raw("count(id) as data"))
                ->whereMonth('created_at', $this->month)
                ->whereYear('created_at', $this->year)
                ->where('status', 'completed')
                ->get();  
        
        return $payments[0]['data'];
    }


    public function getTotalTransactionsPastMonth()
    {   
        $date = \Carbon\Carbon::now();
        $pastMonth =  $date->subMonth()->format('m');

        $payments = Payment::select(DB::raw("count(id) as data"))
                ->whereMonth('created_at', $pastMonth)
                ->whereYear('created_at', $this->year)
                ->where('status', 'completed')
                ->get();  
        
        return $payments[0]['data'];
    }


    public function getTotalTransactionsCurrentYear()
    {   
        $payments = Payment::select(DB::raw("count(id) as data"))                
                ->whereYear('created_at', $this->year)
                ->where('status', 'completed')
                ->get();  
        
        return $payments;
    }


    public function getTotalReferralEarnings()
    {   
        $payments = Referral::select(DB::raw("sum(payment) as data"))                
                ->get();  
        
        return $payments;
    }


    public function getTotalReferralPayouts()
    {   
        $payments = Payout::select(DB::raw("sum(total) as data"))                
                ->where('status', 'completed')
                ->get();  
        
        return $payments;
    }

}