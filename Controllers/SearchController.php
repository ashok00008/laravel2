<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Advertisement;
use App\Jobnotice;
use App\Jobs;
use App\Designation;
use App\Joblocation;
use App\Joborganisation;
use Auth;
use DB;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function joblist(Request $request)
    {
          $keyword = $request->keyword;
           $locations = $request->locations;
           $closing_date = $request->closing_date;
         

        $data = DB::table('jobs') 
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
        ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id')
        ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id')
        ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id')
        ->leftjoin('joborganisations', 'joborganisations.id', '=', 'jobs.org_id')
        ->select('jobs.id','jobs.no_of_vacancy','jobs.description', 'jobs.opening_date', 'jobs.closing_date','jobs.active', 'jobs.location_id','jobs.active', 'joblocations.joblocation', 'joborganisations.organisation','jobs.org_id', 'designations.designation', 'jobtypes.jobtype', 'recruiters.recruiter','advertisements.advertisement_no')
            ->orderBy('jobs.active','1'); 


             if($keyword && $keyword !='') {
            $data->where('jobs.org_id', $keyword);
               }

                if($locations && $locations !='') {
            $data->where('jobs.location_id', $locations);
               }

                if($closing_date && $closing_date !='') {
            $data->whereDate('jobs.closing_date','<=', $closing_date);
               }
               
                $data = $data->paginate(10);

           
       
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function active_vacancy()
    {
        $data = DB::table('jobs') 
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
        ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id')
        ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id')
        ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id')
        ->leftjoin('joborganisations', 'joborganisations.id', '=', 'jobs.org_id')
        ->select('jobs.id','jobs.no_of_vacancy','jobs.description', 'jobs.opening_date', 'jobs.closing_date','jobs.active', 'jobs.location_id','jobs.active', 'joblocations.joblocation', 'joborganisations.organisation', 'designations.designation', 'jobtypes.jobtype', 'recruiters.recruiter','advertisements.advertisement_no')
            ->where('jobs.active','1')
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
        ->select('jobs.id','jobs.no_of_vacancy','jobs.description', 'jobs.opening_date','jobs.active', 'jobs.closing_date','fees.fee as sc_fee', 'g_fees.fee as gen_fee', 'joblocations.joblocation', 'designations.designation', 'jobtypes.jobtype', 'recruiters.recruiter','advertisements.advertisement_no','jobattachments.attachment')
        ->where('jobs.id',$id)
        ->orderBy('jobs.active','1')
        ->first();
       
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function jobnotice($id)
    {
        $data = DB::table('jobnotices') 
            ->select('jobnotices.notice_heading','jobnotices.notice_attachment')
            ->where('jobnotices.adv_id',$id)
            ->get();
       
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function jobView()
    {
        $data = DB::table('jobs') 
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
        ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id')
        ->select('jobs.id','jobs.no_of_vacancy', 'jobs.description','jobs.active', 'jobs.opening_date', 'jobs.closing_date','jobs.location_id', 'joblocations.joblocation', 'joblocations.joblocation', 'designations.designation', 'advertisements.advertisement_no')
        ->limit('20')
        ->orderBy('jobs.id', 'DESC')

        
        ->get();
       
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function jobNoticeView()
    {
        $data = DB::table('jobnotices') 
            ->select('jobnotices.adv_id', 'jobnotices.notice_heading', 'jobnotices.notice_attachment')
            ->limit('10')
            ->orderBy('jobnotices.id', 'DESC')
            ->get();
       
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function searchpost(){
         $search = \Request::get('s');  
        if($search!=null){
          $data = DB::table('jobs')
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
        ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id')
        ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id')
        ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id')
        ->leftjoin('joborganisations', 'joborganisations.id', '=', 'jobs.org_id')
        ->select('jobs.id','jobs.no_of_vacancy','jobs.description', 'jobs.active','jobs.opening_date', 'jobs.closing_date','jobs.location_id', 'joblocations.joblocation', 'joborganisations.organisation', 'designations.designation', 'jobtypes.jobtype', 'recruiters.recruiter','advertisements.advertisement_no')
                ->where('jobs.location_id','LIKE',"%$search%")
                
                //->orWhere('description','LIKE',"%$search%")
                ->get();
            return response()->json([
                'data'=>$data
            ],200);
        }else{
           return $this->joblist();
        }
    }

    public function searchclosingdate(){
         $search = \Request::get('s');   
        if($search!=null){
          $data = DB::table('jobs')
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
        ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id')
        ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id')
        ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id')
        ->leftjoin('joborganisations', 'joborganisations.id', '=', 'jobs.org_id')
        ->select('jobs.id','jobs.no_of_vacancy','jobs.description','jobs.active', 'jobs.opening_date', 'jobs.closing_date','jobs.location_id', 'joblocations.joblocation', 'joborganisations.organisation', 'designations.designation', 'jobtypes.jobtype', 'recruiters.recruiter','advertisements.advertisement_no')
                ->where('jobs.closing_date','<=',$search)
                ->orderBy('jobs.active','1')
                
                //->orWhere('description','LIKE',"%$search%")
                ->get();
            return response()->json([
                'data'=>$data
            ],200);
        }else{
           return $this->joblist();
        }
    }

    public function searchorg(){
         $search = \Request::get('s');  
        if($search!=null){
          $data = DB::table('jobs')
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
        ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id')
        ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id')
        ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id')
        ->leftjoin('joborganisations', 'joborganisations.id', '=', 'jobs.org_id')
        ->select('jobs.id','jobs.no_of_vacancy','jobs.description','jobs.active', 'jobs.opening_date', 'jobs.closing_date','jobs.location_id', 'joblocations.joblocation', 'joborganisations.organisation', 'designations.designation', 'jobtypes.jobtype', 'recruiters.recruiter','advertisements.advertisement_no')
                ->where('jobs.org_id','LIKE',"%$search%")
                ->orderBy('jobs.active','1')
                
                //->orWhere('description','LIKE',"%$search%")
                ->get();
            return response()->json([
                'data'=>$data
            ],200);
        }else{
           return $this->joblist();
        }
    }

     public function location()
    {
         $data = Joblocation::all();
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function organisation()
    {
         $data = Joborganisation::all();
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
