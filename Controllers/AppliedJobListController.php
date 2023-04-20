<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\ApplyJob; 
use DB;
use Auth;

class AppliedJobListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userid = Auth::user()->id;
        if($userid<='0')
        {
           return redirect('/login');
        }
        $data = DB::table('users') 
            ->leftjoin('apply_jobs', 'apply_jobs.user_id', '=', 'users.id')
            ->leftjoin('temp_apply_jobs', 'temp_apply_jobs.user_id', '=', 'users.id')
            ->leftjoin('jobs', 'jobs.id', '=', 'apply_jobs.job_id' )
            ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id' )
            ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id' )
            ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id' )
            ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id' )
            ->leftjoin('joborganisations', 'joborganisations.id', '=', 'jobs.org_id' )
            ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id' )
            ->leftjoin('results', 'results.designation_id', '=', 'jobs.id' )
            ->leftjoin('payments', 'payments.apply_id', '=', 'apply_jobs.id' )
            ->select('users.id','users.fname','users.mname', 'users.lname', 'users.mobile','apply_jobs.application_id' ,'users.registration_id', 'jobs.id','advertisements.advertisement_no',
            'recruiters.recruiter','jobtypes.jobtype','designations.designation','joborganisations.organisation',
            'jobs.no_of_vacancy','joblocations.joblocation','apply_jobs.id as jobid','temp_apply_jobs.id as temp_jobid','results.result_attachment',
            'payments.payment_status','payments.id as payment_id')
            ->where('apply_jobs.user_id',$userid)
            ->where('temp_apply_jobs.user_id',$userid)
            ->get();

             return response()->json([
            'data'=>$data
        ],200);
    }
    public function index_tempApply(){
            
        $userid = Auth::user()->id;
        if($userid<='0')
        {
        return redirect('/login');
        }
        //temp apply jobs
        $temp_apply = DB::table('users') 
        ->leftjoin('temp_apply_jobs', 'temp_apply_jobs.user_id', '=', 'users.id')
        ->leftjoin('jobs', 'jobs.id', '=', 'temp_apply_jobs.job_id' )
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id' )
        ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id' )
        ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id' )
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id' )
        ->leftjoin('joborganisations', 'joborganisations.id', '=', 'jobs.org_id' )
        ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id' )
        ->select('users.id','users.fname','users.mname', 'users.lname', 'users.mobile','temp_apply_jobs.application_id' ,'users.registration_id', 'jobs.id','jobs.active','advertisements.advertisement_no',
        'recruiters.recruiter','jobtypes.jobtype','designations.designation','joborganisations.organisation',
        'jobs.no_of_vacancy','joblocations.joblocation','temp_apply_jobs.id as temp_id','temp_apply_jobs.job_id as temp_job_id','jobs.exam_conducted')
        
        ->where('temp_apply_jobs.user_id',$userid)
        ->get();
        //apply job table
        $apply_jobs = DB::table('users') 
        ->leftjoin('apply_jobs', 'apply_jobs.user_id', '=', 'users.id')
        ->leftjoin('jobs', 'jobs.id', '=', 'apply_jobs.job_id' )
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id' )
        ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id' )
        ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id' )
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id' )
        ->leftjoin('joborganisations', 'joborganisations.id', '=', 'jobs.org_id' )
        ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id' )
        ->leftjoin('examdetails', 'examdetails.application_id', '=', 'apply_jobs.application_id' )
        ->leftjoin('exam_results', 'exam_results.application_id', '=', 'apply_jobs.application_id' )
        ->leftjoin('interview_results', 'interview_results.application_id', '=', 'apply_jobs.application_id' )
        ->select('users.id','users.fname','users.mname', 'users.lname', 'users.mobile','apply_jobs.application_id' ,'apply_jobs.id as apply_id','users.registration_id', 'jobs.id','jobs.active','advertisements.advertisement_no',
        'recruiters.recruiter','jobtypes.jobtype','designations.designation','joborganisations.organisation',
        'jobs.no_of_vacancy','joblocations.joblocation','apply_jobs.id as apply_id','apply_jobs.job_id as apply_job_id','jobs.exam_conducted','apply_jobs.is_screening','examdetails.id as exid','exam_results.exam_status','exam_results.marks','interview_results.interview_status','interview_results.marks as iv_marks')
        
        ->where('apply_jobs.user_id',$userid)
        ->get();

        $merged = $temp_apply->merge($apply_jobs);

         return response()->json([
        'data'=>$merged
    ],200);
    }
   

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
