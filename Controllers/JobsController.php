<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobs;
use App\Jobattachment;
use App\ApplyJob;
use App\User;
use App\UsersDetails;
use App\Exports\JobsExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;
use Auth;
use DB;


class JobsController extends Controller
{   

    public function __construct()
    {
        $this->middleware('auth:admin');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //pdf export
   /* public function pdfexport()
      {
        $data = Emoluments::all();
        $pdf = PDF::loadView('pdf.emolument', compact('data'));
       return $pdf->download('emolument.pdf');
      }
     */
     //csv export
    public function csvexport()
    {

        return Excel::download(new JobsExport, 'joblist.csv');
    }

    public function pdfexport()
      {
        $data = Jobs::all();
        $pdf = PDF::loadView('pdfexport.exportjoblist', compact('data'));
       return $pdf->download('jobs.pdf');
      }


    public function index()
    {
       $data = Jobs::with('advertisement','recruiter','jobtype','designation','joblocation')->orderBy('id','desc')->get();
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

        
       $this->validate($request,[
            'adv_id'=>'required',
            'recruiter_id'=>'required',
            'job_type_id'=>'required',
            'designation_id'=>'required',
            'no_of_vacancy'=>'required',
            'opening_date'=>'required',
            'closing_date'=>'required',
            'description'=>'required',
            'location_id'=>'required',
            'org_id'=>'required',
        
        ]);

        $data = $request->attachment;
        $explode = explode(',',$data);
        $ex = explode('/',$data)[1];
        $extension = explode(';',$ex)[0];
        $valid_extention = ['pdf', 'doc', 'docx'];
        if(in_array($extension, $valid_extention)){
            $data = base64_decode($explode[1]);
            $filename = rand(10000000,999999999).".".$extension;
            $url = public_path().'/uploadimage/'.$filename;
            file_put_contents($url, $data);
            //return response()->json(['success'=>'successfully uploaded']);
        } else {
            return response()->json(['error'=>'please upload pdf file']);
        }

       $jobs = New Jobs();
       $jobs->adv_id = $request->adv_id;
       $jobs->recruiter_id = $request->recruiter_id;
       $jobs->job_type_id = $request->job_type_id;
       $jobs->designation_id = $request->designation_id;
       $jobs->no_of_vacancy = $request->no_of_vacancy;
       $jobs->opening_date = $request->opening_date;
       $jobs->closing_date = $request->closing_date;
       $jobs->description = $request->description;
       $jobs->location_id = $request->location_id;
       $jobs->org_id = $request->org_id;
       $jobs->fee_sc_st_ph = $request->fee_sc_st_ph_id;
       $jobs->fee_gen_obc = $request->fee_gen_obc_id;
       $jobs->interview_location_id = $request->interview_location_id;
       $jobs->exam_conducted = $request->exam_conducted;
       $jobs->interview_conducted = $request->interview_conducted;
       $jobs->created_by = Auth::user()->id;
       $jobs->save();
       $jobidData = $jobs->id;

       if($jobidData){
           $jobattachment = New Jobattachment();
           $jobattachment->adv_id = $request->adv_id;
           $jobattachment->job_id = $jobidData;
           $jobattachment->attachment = $filename; 
           $jobattachment->created_by = Auth::user()->id;
           $jobattachment->save();
            return response()->json([
                'message'=>'success'
            ],200);
       }
       
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
       $data = DB::table('jobs')            
        ->leftjoin('jobattachments', 'jobattachments.job_id', '=', 'jobs.id')
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id')
        ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id')
        ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id')
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
        ->leftjoin('interview_locations', 'interview_locations.id', '=', 'jobs.interview_location_id')

        ->where('jobs.id', $id)
        ->first();
         return response()->json([
            'data'=>$data
        ],200);
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
        $jobattachment = Jobattachment::find($id);

        $this->validate($request,[
            'adv_id'=>'required',
            'recruiter_id'=>'required',
            'job_type_id'=>'required',
            'designation_id'=>'required',
            'no_of_vacancy'=>'required',
            'opening_date'=>'required',
            'closing_date'=>'required',
            'description'=>'required',
            'location_id'=>'required',
            'org_id'=>'required',
        ]);

        if($request->attachment!=$jobattachment->attachment){
            $data = $request->attachment;
            $explode = explode(',',$data);
            $ex = explode('/',$data)[1];
            $extension = explode(';',$ex)[0];
            $valid_extention = ['pdf', 'doc', 'docx'];
            if(in_array($extension, $valid_extention)){
                $data = base64_decode($explode[1]);
                $filename = rand(10000000,999999999).".".$extension;
                $url = public_path().'/uploadimage/'.$filename;
                file_put_contents($url, $data);
                //return response()->json(['success'=>'successfully uploaded']);
            } else {
                return response()->json(['error'=>'please upload pdf file']);
            }

        }else{
            $filename = $jobattachment->attachment;
        }    

        $jobs = Jobs::find($id);
        $jobs->adv_id = $request->adv_id;
        $jobs->recruiter_id = $request->recruiter_id;
        $jobs->job_type_id = $request->job_type_id;
        $jobs->designation_id = $request->designation_id;
        $jobs->no_of_vacancy = $request->no_of_vacancy;
        $jobs->opening_date = $request->opening_date;
        $jobs->closing_date = $request->closing_date;
        $jobs->description = $request->description;
        $jobs->location_id = $request->location_id;
        $jobs->org_id = $request->org_id;
        $jobs->interview_location_id = $request->interview_location_id;
        $jobs->exam_conducted = $request->exam_conducted;
         $jobs->interview_conducted = $request->interview_conducted;
        $jobs->created_by = Auth::user()->id;
        $jobs->save();
        $jobidData = $jobs->id;

         if($jobidData){
           $jobattachment = Jobattachment::find($id);
           $jobattachment->adv_id = $request->adv_id;
           $jobattachment->job_id = $jobidData;
           $jobattachment->attachment = $filename; 
           $jobattachment->created_by = Auth::user()->id;
           $jobattachment->save();
            return response()->json([
                'message'=>'success'
            ],200);
       }
         
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $jobs = Jobs::find($id);
        $jobs->delete();

    }

    public function activate($id)
    {
        $jobs = Jobs::find($id);
       $jobs->active = '1';
           $jobs->save();

    }
    public function deactivate($id)
    {
         $jobs = Jobs::find($id);
       $jobs->active = '0';
           $jobs->save();

    }
    public function getDesignationByAdvertisement(Request $request)
    {
        $advertisement_id = $request->advertisement_id;

        $designations = DB::table('jobs')
            ->leftJoin('designations', 'designations.id', 'jobs.designation_id')
            ->select('designations.id', 'designations.designation')
            ->where('jobs.adv_id', $advertisement_id)
            ->distinct()
            ->get();

        return response()->json(['status' => 'success', 'data' => $designations], 200);
    }

    public function getDesignationByAdvertisementExam(Request $request)
    {
        $advertisement_id = $request->advertisement_id;

        $designations = DB::table('jobs')
            ->leftJoin('designations', 'designations.id', 'jobs.designation_id')
            ->select('designations.id', 'designations.designation')
            ->where('jobs.adv_id', $advertisement_id)
            ->where('jobs.exam_conducted', 'Yes')
            ->distinct()
            ->get();

        return response()->json(['status' => 'success', 'data' => $designations], 200);
    }

    public function getDesignationByAdvertisementInterview(Request $request)
    {
        $advertisement_id = $request->advertisement_id;

        $designations = DB::table('jobs')
            ->leftJoin('designations', 'designations.id', 'jobs.designation_id')
            ->select('designations.id', 'designations.designation')
            ->where('jobs.adv_id', $advertisement_id)
            ->where('jobs.interview_conducted', 'Yes')
            ->distinct()
            ->get();

        return response()->json(['status' => 'success', 'data' => $designations], 200);
    }
}
