<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Jobnotice;
use App\Jobs;
use App\Advertisement;
use App\Designation;
use Auth;
use DB;

class JobnoticeController extends Controller
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
    public function index()
    {
        //$data = Jobnotice::with('jobs')->get();
        $data=DB::table('jobnotices')
            ->leftjoin('advertisements', 'advertisements.id', 'jobnotices.adv_id')
            ->leftjoin('designations', 'designations.id', 'jobnotices.designation_id')
            ->select('jobnotices.id','jobnotices.notice_heading','jobnotices.notice_attachment','advertisements.advertisement_no as adv_id','designations.designation as designation_id')
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
        $this->validate($request,[
            'adv_id'=>'required',
            'designation_id'=>'required',
            'notice_heading'=>'required|min:2|max:100'
        ]);

        $data = $request->notice_attachment;
        $explode = explode(',',$data);
        $ex = explode('/',$data)[1];
        $extension = explode(';',$ex)[0];
        $valid_extention = ['pdf'];
        if(in_array($extension, $valid_extention)){
            $data = base64_decode($explode[1]);
            $filename = rand(10000000,999999999).".".$extension;
            $url = public_path().'/uploadimage/'.$filename;
            file_put_contents($url, $data);
            //return response()->json(['success'=>'successfully uploaded']);
        } else {
            return response()->json(['error'=>'please upload pdf file']);
        }

       $jobnotice = New Jobnotice();
       $jobnotice->adv_id = $request->adv_id;
       $jobnotice->designation_id = $request->designation_id;
       $jobnotice->notice_heading = $request->notice_heading;
       $jobnotice->notice_attachment = $filename;
       $jobnotice->created_by = Auth::user()->id;
       $jobnotice->save();
        return response()->json([
            'message'=>'success'
        ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       $data = DB::table('jobs') 
            ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
            ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
            ->select('jobs.adv_id','jobs.designation_id', 'designations.id', 'designations.designation')
            ->where('jobs.adv_id', $id)
            ->distinct()
            ->get();
         return response()->json([
            'data'=>$data
        ],200);
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
            ->leftjoin('jobnotices', 'jobnotices.designation_id', '=', 'jobs.designation_id')
            ->leftjoin('designations', 'designations.id', '=', 'jobnotices.designation_id')
            ->leftjoin('advertisements', 'advertisements.id', '=', 'jobnotices.adv_id')
            ->select('jobs.adv_id','jobs.designation_id', 'jobnotices.adv_id', 'jobnotices.designation_id', 'jobnotices.notice_heading', 'jobnotices.notice_attachment', 'designations.designation', 'advertisements.advertisement_no')
            ->where('jobnotices.id', $id)
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
        $jobnotice = Jobnotice::find($id);
        $this->validate($request,[
            'adv_id'=>'required',
            'designation_id'=>'required',
            'notice_heading'=>'required|min:2|max:100'
        ]);

        if($request->notice_attachment!=$jobnotice->notice_attachment){
            $data = $request->notice_attachment;
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
            $filename = $jobnotice->notice_attachment;
        }

        $jobnotice = Jobnotice::find($id);
        $jobnotice->adv_id = $request->adv_id;
        $jobnotice->designation_id = $request->designation_id;
        $jobnotice->notice_heading = $request->notice_heading;
        $jobnotice->notice_attachment = $filename;
        $jobnotice->created_by = Auth::user()->id;
        $jobnotice->save();
         return response()->json([
            'message'=>'success'
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $jobnotice = Jobnotice::find($id);
        $jobnotice->delete();
    }
}
