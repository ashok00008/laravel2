<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\ApplyJob;
use App\CoursesCentre;
use App\Jobs;
use App\MitCourseRegister;
use App\MitCourses;
use App\Payment;
use App\User;
use App\OnlinePayment;
use App\Recruiter;
use App\TempApplyJob;
use Auth;
use Carbon\Carbon;


class DashboardController extends Controller
{
    public function getStateWiseCandidate(Request $request)
    {
        $year = $request->year;
        $month = $request->month;
        $currentYear = date('Y');

        $candidates = DB::table('apply_jobs')
            ->leftjoin('user_addresses', 'user_addresses.user_id', 'apply_jobs.user_id')
            ->leftjoin('states', 'states.id', 'user_addresses.c_state_id')
            ->select('states.name', 'states.id', DB::raw('count(*) as total'))
            ->groupBy('states.name',  'states.id');

        if ($year && $year != '') {
            $candidates->whereYear('apply_jobs.created_at', $year);
        }

        if ($year && $year !='' && $month && $month !='') {
            $candidates->whereYear('apply_jobs.created_at', $year)
                ->whereMonth('apply_jobs.created_at', $month);
        }

        if ($year == '' && $month && $month != '') {
            $candidates->whereYear('apply_jobs.created_at', $currentYear)
                ->whereMonth('apply_jobs.created_at', $month);
        }

        $candidates = $candidates->get();

        return response()->json(['status' => 'success', 'data' => $candidates], 200);
    }

     public function getapplyCandidate($year)
    {
         $userid = Auth::user()->id;
           $data = DB::table('apply_jobs')
            ->where('user_id', $userid)
             ->select(DB::raw('count(id) as total'), DB::raw('MONTHNAME(created_at) as month'))
         ->groupBy('month')
         ->whereYear('created_at', $year)
         //->orderBy('created_at')
         ->get();
         $data=array_reverse(json_decode(json_encode($data), true));

        return response()->json(['status' => 'success', 'data' => $data], 200);
    }


    public function getMonthWiseApplication($year)
    {
          
           $data = DB::table('apply_jobs')
             ->select(DB::raw('count(id) as total'), DB::raw('MONTHNAME(created_at) as month'))
         ->groupBy('month')
         ->whereYear('created_at', $year)
         ->get();
         
         $data=array_reverse(json_decode(json_encode($data), true));
         
        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    public function getVacancybyRecruiter(Request $request)
    {
          

        $year = $request->year;
        $month = $request->month;
        $currentYear = date('Y');

        $data = DB::table('jobs')
            ->leftjoin('recruiters','recruiters.id','jobs.recruiter_id')
             ->select(DB::raw('sum(no_of_vacancy) as total'), DB::raw('(recruiters.recruiter) as recruiter'))
         ->groupBy('recruiter');

        if ($year && $year != '') {
            $data->whereYear('jobs.created_at', $year);
        }

        if ($year && $year !='' && $month && $month !='') {
            $data->whereYear('jobs.created_at', $year)
                ->whereMonth('jobs.created_at', $month);
        }

        if ($year == '' && $month && $month != '') {
            $data->whereYear('jobs.created_at', $currentYear)
                ->whereMonth('jobs.created_at', $month);
        }

        $data = $data->get();

        return response()->json(['status' => 'success', 'data' => $data], 200);
    }

    public function getApplicationPaymentSuccess(Request $request)
    {
          

        $year = $request->year;
        $month = $request->month;
        $currentYear = date('Y');

        
  $candidates = DB::table('apply_jobs')
            ->select( DB::raw("DATE_FORMAT(created_at,'%Y-%m') as date"),DB::raw('count(id) as value'))
            ->groupBy('date');


            if($year == '' && $month=='')
            {
            
            $candidates->whereYear('created_at',$currentYear);


            }

        if ($year && $year != '') {
            $candidates->whereYear('apply_jobs.created_at', $year);
        }

        if ($year && $year !='' && $month && $month !='') {
            $candidates->whereYear('apply_jobs.created_at', $year)
                ->whereMonth('apply_jobs.created_at', $month);
        }

        if ($year == '' && $month && $month != '') {
            $candidates->whereYear('apply_jobs.created_at', $currentYear)
                ->whereMonth('apply_jobs.created_at', $month);
        }

        $candidates = $candidates->get();

        return response()->json([ 'data' => $candidates], 200);
    }

    public function newUserThisMonth()
    {
          
           $data = DB::table('users')
             ->select(DB::raw('count(id) as total'), DB::raw('MONTHNAME(created_at) as month'))
         ->groupBy('month')
         ->whereYear('created_at', Carbon::now()->year)
         ->whereMonth('created_at', Carbon::now()->month)    
         ->orderBy('created_at')
         ->get();

         $data2 = User::whereYear('created_at', Carbon::now()->year)
         ->whereMonth('created_at', Carbon::now()->month) 
         ->count();

         

        return response()->json(['data' => $data, 'data2' => $data2], 200);
    }

    public function getDayWiseAllApplicationReceived(Request $request)
    {
        $year = $request->year;
        $to = $request->to;
        $from = $request->from;
        $advertisementId = $request->advertisement;
        $currentYear = date('Y');
        $currentDate = date('Y-m-d');

        $applications = DB::table('apply_jobs')
            ->leftJoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftJoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->select(DB::raw('count(*) as total'), DB::Raw('DATE(apply_jobs.created_at) date'))
            ->groupBy(['date']);

        $tempApplication = DB::table('temp_apply_jobs')
            ->leftJoin('jobs', 'jobs.id', 'temp_apply_jobs.job_id')
            ->leftJoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->select(DB::raw('count(*) as total'), DB::Raw('DATE(temp_apply_jobs.created_at) date'))
            ->groupBy(['date']);

        if ($advertisementId && $advertisementId != '') {
            $applications->where('advertisements.id', $advertisementId);
            $tempApplication->where('advertisements.id', $advertisementId);
        }

        if ($year != '') {
            $applications->whereYear('apply_jobs.created_at', $year);
            $tempApplication->whereYear('temp_apply_jobs.created_at', $year);
        }

        if ($from != '') {
            $applications->whereBetween('apply_jobs.created_at', [$from, $currentDate]);
            $tempApplication->whereBetween('temp_apply_jobs.created_at', [$from, $currentDate]);
        }

        if ($to != '') {
            $applications->whereBetween('apply_jobs.created_at', [$to, $currentDate]);
            $tempApplication->whereBetween('temp_apply_jobs.created_at', [$to, $currentDate]);
        }

        if ($from != '' && $to != '') {
            $applications->whereBetween('apply_jobs.created_at', [$from, $to]);
            $tempApplication->whereBetween('temp_apply_jobs.created_at', [$from, $to]);
        }

        $applications = $applications->get();

        $tempApplication = $tempApplication->get();

        $applications = $applications->merge($tempApplication)->sortBy('date');

        $applications = $applications->groupBy('date');

        $results = [];

        foreach ($applications as $items) {
            $results[] = [
                'value' => $items->sum('total'),
                'date' => $items->first()->date
            ];
        }

        return $results;
    }
    public function paymentThisMonth()
    {
          
           $data = DB::table('online_payments')
             ->select(DB::raw('count(id) as total'), DB::raw('MONTHNAME(created_at) as month'))
         ->groupBy('month')
         ->whereYear('created_at', Carbon::now()->year)
         ->whereMonth('created_at', Carbon::now()->month)    
         ->orderBy('created_at')
         ->get();

         $data2 = User::whereYear('created_at', Carbon::now()->year)
         ->whereMonth('created_at', Carbon::now()->month) 
         ->count();

         

        return response()->json(['data' => $data, 'data2' => $data2], 200);
    }
    public function  userdashboard(){

        $userid = Auth::user()->id;

           $data['active_vaccancy'] = Jobs::where('active', '1')
           ->count();
            $data['closed_vaccancy'] = Jobs::where('active', '0')
           ->count();

           $data['apply_jobs'] = ApplyJob::where('user_id', $userid)
           ->count();

            $data['payment_done'] = OnlinePayment::where('user_id', $userid)
           ->count();

             $data['payment_pending'] = TempApplyJob::where('user_id', $userid)
           ->count();
       

        return response()->json([
           'data'=>$data
       ],200);
   }

   public function  admindashboard(){

        // $userid = Auth::user()->id;

           $data['active_vaccancy'] = Jobs::where('active', '1')
           ->count();
            $data['closed_vaccancy'] = Jobs::where('active', '0')
           ->count();

           $data['apply_jobs'] = ApplyJob::count();

            $data['payment_done'] = OnlinePayment::count();

            $data['payment_pending'] = TempApplyJob::count();
            $data['total_job_post'] = Jobs::count();
            $data['total_users'] = User::count();
            $data['total_recruiters'] = Recruiter::count();
            $data['total_registration'] = MitCourseRegister::count();
            $data['total_centres'] = CoursesCentre::count();
            $data['total_courses'] = MitCourses::count(); //Courses total

        return response()->json([
           'data'=>$data
       ],200);
   }

   public function dayWiseEarningByApplication()
    {
        $earning = OnlinePayment::select(
            DB::raw('sum(amount) as value'),
            DB::raw("DATE_FORMAT(created_at,'%Y-%m-%d') as date")
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('date')
            ->orderBy('created_at', 'ASC')
            ->get();

        return $earning;
    }
   
    public function getPaymentByApplicationData(Request $request){
     $year = $request->year;
        $month = $request->month;

        $applications = DB::table('apply_jobs')
            ->leftJoin('online_payments', 'online_payments.application_id', 'apply_jobs.application_id')
            ->select(DB::raw('count(*) as total'), DB::Raw("DATE_FORMAT(apply_jobs.created_at,'%Y-%m-%d') date"), DB::raw("SUM(amount) as fee"))
            ->groupBy(['date']);

        if ($year && $year != '') {
            $applications->whereYear('apply_jobs.created_at', $year);
        }

        if ($month && $month != '') {
            $applications->whereMonth('apply_jobs.created_at', $month);
        }

        $applications = $applications->get();

        return $applications;

}
    public function getAppliedButNotPaid(Request $request){

        // $data = DB::table('jobs')
        // ->leftJoin('apply_jobs', 'apply_jobs.job_id', 'jobs.id')
        // ->leftJoin('temp_apply_jobs', 'temp_apply_jobs.job_id', 'jobs.id')
        // ->leftJoin('advertisements', 'advertisements.id', 'jobs.adv_id')
        // ->leftJoin('designations', 'designations.id', 'jobs.designation_id')
        // ->select(DB::raw('count(temp_apply_jobs.id) as temp_jobs'), DB::raw('count(apply_jobs.id) as apply_jobs'),'designations.designation as country')
        // ->groupBy('country')
        // ->get();

        $adv_id = $request->adv_id;
        $postion_id = $request->position_id;

        $applied = array();
        $tempApplied = array();

        if($postion_id && $postion_id != '') {

            $data = DB::table('jobs')
                ->leftJoin('apply_jobs', 'apply_jobs.job_id', 'jobs.id')
                ->leftJoin('advertisements', 'advertisements.id', 'jobs.adv_id')
                ->leftJoin('designations', 'designations.id', 'jobs.designation_id')
                ->select(DB::raw('count(apply_jobs.id) as apply_jobs'))
                ->where('jobs.adv_id', $adv_id)
                ->where('jobs.designation_id', $postion_id)
                ->get();

            $data = $data->map(function($q) {
                $applied['total_jobs'] = $q->apply_jobs;
                $applied['type'] = 'Applied';
                return $applied;
            });

            $data1 = DB::table('jobs')
                ->leftJoin('temp_apply_jobs', 'temp_apply_jobs.job_id', 'jobs.id')
                ->leftJoin('advertisements', 'advertisements.id', 'jobs.adv_id')
                ->leftJoin('designations', 'designations.id', 'jobs.designation_id')
                ->select(DB::raw('count(temp_apply_jobs.id) as temp_apply_jobs'))
                ->where('jobs.adv_id', $adv_id)
                ->where('jobs.designation_id', $postion_id)
                ->get();


            $data1 = $data1->map(function($q) {
                $tempApplied['total_jobs'] = $q->temp_apply_jobs;
                $tempApplied['type'] = 'Applied(But Not Paid)';
                return $tempApplied;
            });

        } else {
            $data = DB::table('jobs')
                ->leftJoin('apply_jobs', 'apply_jobs.job_id', 'jobs.id')
                ->leftJoin('advertisements', 'advertisements.id', 'jobs.adv_id')
                ->leftJoin('designations', 'designations.id', 'jobs.designation_id')
                ->select(DB::raw('count(apply_jobs.id) as apply_jobs'))
                ->get();

            $data = $data->map(function($q) {
                $applied['total_jobs'] = $q->apply_jobs;
                $applied['type'] = 'Applied';
                return $applied;
            });

            $data1 = DB::table('jobs')
                ->leftJoin('temp_apply_jobs', 'temp_apply_jobs.job_id', 'jobs.id')
                ->leftJoin('advertisements', 'advertisements.id', 'jobs.adv_id')
                ->leftJoin('designations', 'designations.id', 'jobs.designation_id')
                ->select(DB::raw('count(temp_apply_jobs.id) as temp_apply_jobs'))
                ->get();


            $data1 = $data1->map(function($q) {
                $tempApplied['total_jobs'] = $q->temp_apply_jobs;
                $tempApplied['type'] = 'Applied(But Not Paid)';
                return $tempApplied;
            });

        }

        return $newData = $data->merge($data1);

        // $data = DB::table('temp_apply_jobs')
        // ->leftJoin('apply_jobs', 'apply_jobs.id', 'temp_apply_jobs.id')
        // ->leftJoin('jobs', 'jobs.id', 'temp_apply_jobs.job_id')
        // ->leftJoin('advertisements', 'advertisements.id', 'jobs.adv_id')
        // ->leftJoin('designations', 'designations.id', 'jobs.designation_id')
        // ->select(DB::raw('count(temp_apply_jobs.id) as litres'), DB::raw('count(apply_jobs.id) as bottles'),'designations.designation as country','advertisements.advertisement_no as adv')
        // ->groupBy('country')
        // ->groupBy('adv')
        // ->get();
    
        return response()->json($data);
    }
    public function getTotalRegisterUser(Request $request){
        $year = $request->year;
        $from = $request->from;
        $month = $request->month;
        $currentYear = date('Y');
        $currentDate = date('Y-m-d');

        $data = DB::table('users')
            ->select(DB::raw('count(id) as steps'), DB::Raw("DATE_FORMAT(created_at,'%Y-%m-%d') date"))
            ->groupBy('date');

        if($year && $year != '') {
            $data = $data->whereYear('created_at', $year);
        } 
        
        if($month && $month != '') {
            $data = $data->whereMonth('created_at', $month);
        }

        if($from && $from != '' && $month && $month != '' && $year && $year != '') {
            $data = $data->whereDate('created_at', $from)->whereMonth('created_at', $month)->whereYear('created_at', $year);
        }

        

        $data = $data->get();

        return response()->json($data);

    }
}
