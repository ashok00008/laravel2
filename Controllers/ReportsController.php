<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Mail;
use App\Examdetail;

class ReportsController extends Controller
{
    public function applicationLists(Request $request)
    {
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
        $state_id = $request->state_id;
        $city_id = $request->city_id;
        $from = $request->from;
        $to = $request->to;
        $today = Carbon::today();


        $applicationLists = DB::table('apply_jobs')
            ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->select('apply_jobs.application_id', 'advertisements.advertisement_no', 'users.id', 'users.fname',
            'users.lname', 'users.mobile', 'users_details.father_name', 'users_details.dob', 'users_details.email1', 'users_details.email2',
            'users_details.mobile1', 'user_exps.total_exp_year', 'user_exps.total_exp_month', 'categories.cat_name', 'designations.designation')
            ->whereNull('is_screening');
            
        if($advertisement_id && $advertisement_id !='') {
            $applicationLists->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $applicationLists->where('jobs.designation_id', $position_id);
        }

        if($state_id && $state_id !='') {
            $applicationLists->where('user_addresses.c_state_id', $state_id);
        }

        if($city_id && $city_id !='') {
            $applicationLists->where('user_addresses.c_city_id', $city_id);
        }

        if($from && $from !='' && $to == '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $today));
        }

        if($from && $from !='' && $to != '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $to));
        }

        $applicationLists = $applicationLists->paginate(25);

        return response()->json(['status' => 'success', 'data' => $applicationLists], 200);
    }

    public function exportApplicationLists(Request $request)
    {
        $export_type = $request->export_in;
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
        $state_id = $request->state_id;
        $city_id = $request->city_id;
        $from = $request->from;
        $to = $request->to;
        $today = Carbon::today();
        $date = date('d-m-Y');
        $type = '';
        $extension = '';

        if($export_type == 'excel') {
            $type = 'text/csv';
            $extension = 'csv';
        }

        $applicationLists = DB::table('apply_jobs')
            ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->select('apply_jobs.application_id', 'advertisements.advertisement_no', 'users.fname',
            'users.lname', 'users.mobile', 'users_details.father_name', 'users_details.dob', 'users_details.email1',
            'users_details.mobile1', 'user_exps.total_exp_year', 'user_exps.total_exp_month', 'categories.cat_name', 'designations.designation')
            ->whereNull('apply_jobs.is_screening');

        if($advertisement_id && $advertisement_id !='') {
            $applicationLists->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $applicationLists->where('jobs.designation_id', $position_id);
        }

        if($state_id && $state_id !='') {
            $applicationLists->where('user_addresses.c_state_id', $state_id);
        }

        if($city_id && $city_id !='') {
            $applicationLists->where('user_addresses.c_city_id', $city_id);
        }

        if($from && $from !='' && $to == '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $today));
        }

        if($from && $from !='' && $to != '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $to));
        }

        $applicationLists = $applicationLists->get();

        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => $type,
            'Content-Disposition' => 'attachment; filename=' .$date. '_' .'application_lists.' .$extension,
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];

        $list = collect($applicationLists)->map(function($x){ return (array) $x; })->toArray();

        # add headers for each column in the CSV download
        array_unshift($list, array_keys($list[0]));

        $callback = function() use ($list)
        {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return Response::stream($callback, 200, $headers);

        //return response()->json(['status' => 'success', 'data' => $applicationLists], 200);
    }

    public function shortlistedApplicationLists(Request $request)
    {
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
        $state_id = $request->state_id;
        $city_id = $request->city_id;
        $from = $request->from;
        $to = $request->to;
        $today = Carbon::today();


        $shortlistApplicationLists = DB::table('apply_jobs')
            ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->select('apply_jobs.application_id', 'advertisements.advertisement_no', 'users.id', 'users.fname',
                'users.lname', 'users.mobile', 'users_details.father_name', 'users_details.dob', 'users_details.email1', 'users_details.email2',
                'users_details.mobile1', 'user_exps.total_exp_year', 'user_exps.total_exp_month', 'categories.cat_name', 'designations.designation')
            ->where('apply_jobs.is_screening', 'shortlist');

        if($advertisement_id && $advertisement_id !='') {
            $shortlistApplicationLists->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $shortlistApplicationLists->where('jobs.designation_id', $position_id);
        }

        if($state_id && $state_id !='') {
            $shortlistApplicationLists->where('user_addresses.c_state_id', $state_id);
        }

        if($city_id && $city_id !='') {
            $shortlistApplicationLists->where('user_addresses.c_city_id', $city_id);
        }

        if($from && $from !='' && $to == '') {
            $shortlistApplicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $today));
        }

        if($from && $from !='' && $to != '') {
            $shortlistApplicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $to));
        }

        $shortlistApplicationLists = $shortlistApplicationLists->paginate(25);

        return response()->json(['status' => 'success', 'data' => $shortlistApplicationLists], 200);
    }

    public function exportShortlistedApplicationLists(Request $request)
    {
        $export_type = $request->export_in;
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
        $state_id = $request->state_id;
        $city_id = $request->city_id;
        $from = $request->from;
        $to = $request->to;
        $today = Carbon::today();
        $date = date('d-m-Y');
        $type = '';
        $extension = '';

        if($export_type == 'excel') {
            $type = 'text/csv';
            $extension = 'csv';
        }

        $exportShortlistApplicant = DB::table('apply_jobs')
            ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->select('apply_jobs.application_id', 'advertisements.advertisement_no', 'users.fname',
                'users.lname', 'users.mobile', 'users_details.father_name', 'users_details.dob', 'users_details.email1',
                'users_details.mobile1', 'user_exps.total_exp_year', 'user_exps.total_exp_month', 'categories.cat_name', 'designations.designation')
            ->where('apply_jobs.is_screening', 'shortlist');

        if($advertisement_id && $advertisement_id !='') {
            $exportShortlistApplicant->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $exportShortlistApplicant->where('jobs.designation_id', $position_id);
        }

        if($state_id && $state_id !='') {
            $exportShortlistApplicant->where('user_addresses.c_state_id', $state_id);
        }

        if($city_id && $city_id !='') {
            $exportShortlistApplicant->where('user_addresses.c_city_id', $city_id);
        }

        if($from && $from !='' && $to == '') {
            $exportShortlistApplicant->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $today));
        }

        if($from && $from !='' && $to != '') {
            $exportShortlistApplicant->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $to));
        }

        $exportShortlistApplicant = $exportShortlistApplicant->get();

        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => $type,
            'Content-Disposition' => 'attachment; filename=' .$date. '_' .'shortlist-applicant_lists.' .$extension,
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];

        $list = collect($exportShortlistApplicant)->map(function($x){
            return [
                'Application Id' => $x->application_id,
                'Advertisement No' => $x->advertisement_no,
                'Name' => $x->fname. ' ' .$x->lname,
                'Mobile 1' => $x->mobile,
                'Mobile 2' => $x->mobile1,
                'Father Name' => $x->father_name,
                'Email' => $x->email1,
                'DOB' => $x->dob,
                'Total Exp Year' => $x->total_exp_year,
                'Total Exp Month' => $x->total_exp_month,
                'Category' => $x->cat_name,
                'Designation' => $x->designation,
            ];

        })->toArray();

        # add headers for each column in the CSV download
        array_unshift($list, array_keys($list[0]));

        $callback = function() use ($list)
        {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function rejectedApplicationLists(Request $request)
    {
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
        $state_id = $request->state_id;
        $city_id = $request->city_id;
        $from = $request->from;
        $to = $request->to;
        $today = Carbon::today();


        $rejectApplicationLists = DB::table('apply_jobs')
            ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->select('apply_jobs.application_id', 'advertisements.advertisement_no', 'users.id', 'users.fname',
                'users.lname', 'users.mobile', 'users_details.father_name', 'users_details.dob', 'users_details.email1', 'users_details.email2',
                'users_details.mobile1', 'user_exps.total_exp_year', 'user_exps.total_exp_month', 'categories.cat_name', 'designations.designation')
            ->where('apply_jobs.is_screening', 'reject');

        if($advertisement_id && $advertisement_id !='') {
            $rejectApplicationLists->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $rejectApplicationLists->where('jobs.designation_id', $position_id);
        }

        if($state_id && $state_id !='') {
            $rejectApplicationLists->where('user_addresses.c_state_id', $state_id);
        }

        if($city_id && $city_id !='') {
            $rejectApplicationLists->where('user_addresses.c_city_id', $city_id);
        }

        if($from && $from !='' && $to == '') {
            $rejectApplicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $today));
        }

        if($from && $from !='' && $to != '') {
            $rejectApplicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $to));
        }

        $rejectApplicationLists = $rejectApplicationLists->paginate(25);

        return response()->json(['status' => 'success', 'data' => $rejectApplicationLists], 200);
    }

    public function exportRejectedApplicationLists(Request $request)
    {
        $export_type = $request->export_in;
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
        $state_id = $request->state_id;
        $city_id = $request->city_id;
        $from = $request->from;
        $to = $request->to;
        $today = Carbon::today();
        $date = date('d-m-Y');
        $type = '';
        $extension = '';

        if($export_type == 'excel') {
            $type = 'text/csv';
            $extension = 'csv';
        }

        $exportShortlistApplicant = DB::table('apply_jobs')
            ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->select('apply_jobs.application_id', 'advertisements.advertisement_no', 'users.fname',
                'users.lname', 'users.mobile', 'users_details.father_name', 'users_details.dob', 'users_details.email1',
                'users_details.mobile1', 'user_exps.total_exp_year', 'user_exps.total_exp_month', 'categories.cat_name', 'designations.designation')
            ->where('apply_jobs.is_screening', 'reject');

        if($advertisement_id && $advertisement_id !='') {
            $exportShortlistApplicant->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $exportShortlistApplicant->where('jobs.designation_id', $position_id);
        }

        if($state_id && $state_id !='') {
            $exportShortlistApplicant->where('user_addresses.c_state_id', $state_id);
        }

        if($city_id && $city_id !='') {
            $exportShortlistApplicant->where('user_addresses.c_city_id', $city_id);
        }

        if($from && $from !='' && $to == '') {
            $exportShortlistApplicant->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $today));
        }

        if($from && $from !='' && $to != '') {
            $exportShortlistApplicant->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $to));
        }

        $exportShortlistApplicant = $exportShortlistApplicant->get();

        $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => $type,
            'Content-Disposition' => 'attachment; filename=' .$date. '_' .'reject-applicant_lists.' .$extension,
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];

        $list = collect($exportShortlistApplicant)->map(function($x){
            return [
                'Application Id' => $x->application_id,
                'Advertisement No' => $x->advertisement_no,
                'Name' => $x->fname. ' ' .$x->lname,
                'Mobile 1' => $x->mobile,
                'Mobile 2' => $x->mobile1,
                'Father Name' => $x->father_name,
                'Email' => $x->email1,
                'DOB' => $x->dob,
                'Total Exp Year' => $x->total_exp_year,
                'Total Exp Month' => $x->total_exp_month,
                'Category' => $x->cat_name,
                'Designation' => $x->designation,
            ];

        })->toArray();

        # add headers for each column in the CSV download
        array_unshift($list, array_keys($list[0]));

        $callback = function() use ($list)
        {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function applicationListsMail(Request $request)
    {
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
        $state_id = $request->state_id;
        $city_id = $request->city_id;
        $from = $request->from;
        $to = $request->to;
        $today = Carbon::today();
        
         $users = Examdetail::All('application_id');
    foreach ($users as $user) {
                $data[] = $user->application_id;
            }


        $applicationLists = DB::table('apply_jobs')
            ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->whereNotIn('apply_jobs.application_id', $data)
            ->select('apply_jobs.id','apply_jobs.application_id', 'advertisements.advertisement_no', 'users.fname',
            'users.lname', 'users.mobile', 'users_details.father_name', 'users_details.dob', 'users_details.email1',
            'users_details.mobile1', 'user_exps.total_exp_year', 'user_exps.total_exp_month', 'categories.cat_name', 'designations.designation');

        if($advertisement_id && $advertisement_id !='') {
            $applicationLists->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $applicationLists->where('jobs.designation_id', $position_id);
        }

        if($state_id && $state_id !='') {
            $applicationLists->where('user_addresses.c_state_id', $state_id);
        }

        if($city_id && $city_id !='') {
            $applicationLists->where('user_addresses.c_city_id', $city_id);
        }

        if($from && $from !='' && $to == '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $today));
        }

        if($from && $from !='' && $to != '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $to));
        }

        $applicationLists = $applicationLists->get();

        return response()->json(['status' => 'success', 'data' => $applicationLists], 200);
    }

    public function send(Request $request){
         
        $checkedNames = $request->checkedNames;
         $inteviewdate = $request->inteviewdate;
        $location = $request->location;
         //$explode = implode(',', $checkedNames);
          // $applicationLists = DB::table('apply_jobs')
         //   ->leftjoin('users_details', 'users_details.user_id', 'apply_jobs.user_id')
          //   ->select('apply_jobs.id','apply_jobs.job_id','apply_jobs.application_id','apply_jobs.user_reg_id', 'users.fname',
          //    'users_details.email1' )->where('apply_jobs.id',$apply_id)->first();

         $size_explode=sizeof($checkedNames);
         for($i=0;$i<$size_explode;$i++)
         {
              $apply_id = $checkedNames[$i];
             

          $applicationLists = DB::table('apply_jobs')
          ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('users_details', 'users_details.user_id', 'apply_jobs.user_id')
            ->select('apply_jobs.id','apply_jobs.job_id','apply_jobs.application_id','apply_jobs.user_id','apply_jobs.user_reg_id', 'users.fname',
             'users_details.email1' )->where('apply_jobs.id',$apply_id)->first();

                   $jobs_id = $applicationLists->job_id;
                   $appli_id = $applicationLists->application_id;
                   $userid = $applicationLists->user_id;
                    $regid = $applicationLists->user_reg_id;

                    $check = Examdetail::where('application_id',$appli_id)->first();
                    if($check !== null)
                    {
                        
                    }
                    else{
                $exam = New Examdetail();
                $exam->application_id =  $appli_id;
                $exam->user_id = $userid;
                $exam->user_reg_id = $regid;
                $exam->job_id = $jobs_id;
                $exam->exam_datetime =  $inteviewdate;
                $exam->venue = $location;
                $exam->status = '0';
                $exam->save();

                


             $toEmail = $applicationLists->email1;
          $data= ['name'=>$applicationLists->fname,'email'=>$applicationLists->email1,'interviewdate'=>$inteviewdate,'location'=> $location];

           Mail::send('SendMail.interviewDetails', $data, function ($message) use ($toEmail) {
            $message->to($toEmail)->subject('Scheduled Interview');
            $message->from('info@beciljobs.com', 'BecilJobs.com');
        });

       }
            
   
         }

          if ($size_explode<1) {
           
            return response()->json(['data' => 'Please Select atleast one candidate'], 201);
        }
       
       return response()->json(['data' => 'Message Send successfully'], 200);
       

    }

    //not paid report
    public function notpaidsend(Request $request){
         
        $checkedNames = $request->checkedNames;
        $location = $request->location;
       
         $size_explode=sizeof($checkedNames);
         for($i=0;$i<$size_explode;$i++)
         {
              $apply_id = $checkedNames[$i];
             

         $applicationLists = DB::table('temp_apply_jobs')
            ->leftjoin('users', 'users.id', 'temp_apply_jobs.user_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->select('temp_apply_jobs.id','temp_apply_jobs.application_id', 'users.fname',
            'users.lname', 'users.mobile', 'users_details.email1')->where('temp_apply_jobs.id',$apply_id)->first();
             


         


             $toEmail = $applicationLists->email1;
          $data= ['name'=>$applicationLists->fname,'email'=>$applicationLists->email1,'location'=> $location];

           Mail::send('SendMail.notpaid', $data, function ($message) use ($toEmail) {
            $message->to($toEmail)->subject('Payment Reminder');
            //$message->from('info@beciljobs.com', 'BecilJobs.com');
            //$message->from(env('MAIL_USERNAME'), 'BecilJobs.com');
            $message->from(env('TEST_USEREMAIL'), 'BecilJobs.com');
        });

              //send sms
            $api_key = env('SMS_API_KEY');
    $from = env('SMS_FROM');
    $campaign = env('SMS_CAMPAIGN');
    $routeid = env('SMS_ROUTE_ID');
    $smstype = env('SMS_TYPE');
            $contacts =$applicationLists->mobile;
            $name =$applicationLists->fname;
           
            $msg="Dear ".$name." , ".$location."";
            $sms_text = urlencode($msg);
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, env('SMS_CURL_URL'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=".$campaign."&routeid=".$routeid."&type=".$smstype."&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text);
            $response = curl_exec($ch);
            curl_close($ch);

       }
           
   
       

          if ($size_explode<1) {
           
            return response()->json(['data' => 'Please Select atleast one candidate'], 201);
        }
       
       return response()->json(['data' => 'Message Send successfully'], 200);
       

    }  




public function applicationListsnotpaid(Request $request)
    {
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
       
     


        $applicationLists = DB::table('temp_apply_jobs')
            ->leftjoin('users', 'users.id', 'temp_apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'temp_apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->select('temp_apply_jobs.id','temp_apply_jobs.application_id', 'advertisements.advertisement_no', 'users.fname',
            'users.lname', 'users.mobile', 'users_details.father_name', 'users_details.dob', 'users_details.email1', 'users_details.email2',
            'users_details.mobile1', 'user_exps.total_exp_year', 'user_exps.total_exp_month', 'categories.cat_name', 'designations.designation')
            ;

        if($advertisement_id && $advertisement_id !='') {
            $applicationLists->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $applicationLists->where('jobs.designation_id', $position_id);
        }

     
        $applicationLists = $applicationLists->get();

        return response()->json(['status' => 'success', 'data' => $applicationLists], 200);
    }

}
