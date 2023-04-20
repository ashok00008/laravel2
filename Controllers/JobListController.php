<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobnotice;
use App\Jobs;
use App\Advertisement;
use App\Designation;
use App\Joblocation;
use DB;
use Auth;


class JobListController extends Controller
{   
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userid = Auth::user()->id;
        $data1 = DB::table('jobs') 
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
        ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id')
        ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id')
        ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id')
        ->select('jobs.id','jobs.no_of_vacancy','jobs.description','jobs.active', 'jobs.opening_date', 'jobs.closing_date','jobs.location_id', 'joblocations.joblocation', 'designations.designation', 'jobtypes.jobtype', 'recruiters.recruiter','advertisements.advertisement_no')
        ->orderBy('jobs.active','1')
            ->paginate(10);

          

       
        return response()->json([
            'data'=>$data1
        ],200);
    }

    public function applied()
    {
        $userid = Auth::user()->id;
            $data = DB::table('apply_jobs') 
            ->select( 'apply_jobs.job_id as id')
            ->where('apply_jobs.user_id',$userid)
            ->get();
   
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function jobdesc($id)
    {
        $data = DB::table('jobs') 
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
        ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id')
        ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id')
        ->leftjoin('jobattachments' , 'jobattachments.job_id' , '=' , 'jobs.id')
        ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id')
        ->leftjoin('fees', 'fees.id', '=', 'jobs.fee_sc_st_ph')
        ->leftjoin('fees as g_fees', 'g_fees.id', '=', 'jobs.fee_gen_obc')
        ->select('jobs.id','jobs.no_of_vacancy','jobs.description','jobs.active', 'jobs.opening_date', 'jobs.closing_date',
        'jobs.location_id','fees.fee as sc_fee', 'g_fees.fee as gen_fee', 'joblocations.joblocation', 'designations.designation', 'jobtypes.jobtype', 'recruiters.recruiter','advertisements.advertisement_no','jobattachments.attachment')
        ->where('jobs.id',$id)
        ->first();
       
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function jobnotice($id)
    {
        $data = DB::table('jobnotices') 
            ->leftjoin('jobs' , 'jobs.designation_id' , '=' , 'jobnotices.designation_id')
            ->select('jobnotices.notice_heading','jobnotices.notice_attachment')
            ->where('jobs.id',$id)
            ->get();
       
        return response()->json([
            'data'=>$data
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
