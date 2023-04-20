<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\InterviewLocation;
Use Auth;

class InteviewLocationController extends Controller
{
    
    public function index()
    {
        $data = InterviewLocation::all();
        return response()->json([
            'data'=>$data
        ],200);
    }
    public function getActiveInterviewLocation()
    {
        $data = InterviewLocation::all()->where('status',1);
        return response()->json([
            'data'=>$data
        ],200);
    }

  
    
    public function store(Request $request)
    {
        $this->validate($request,[
            'interviewlocation'=>'required'
        ]);
       $interviewlocation = New InterviewLocation();
       $interviewlocation->interviewlocation = $request->interviewlocation;
       $interviewlocation->created_by = Auth::user()->id;
       $interviewlocation->save();
        return response()->json([
            'message'=>'success'
        ],200);
    }
    

   
   
    public function edit($id)
    {
        $data = InterviewLocation::find($id);
        return response()->json([
            'data'=>$data
        ],200);
    }
   
    public function update(Request $request)
    {
        //echo "hello";
        $id = $request->params['int_id'];
        $int_loc_venue = $request->params['int_loc_ven'];
        $update = InterviewLocation::where('id',$id)->update([
            'interviewlocation'=>$int_loc_venue
        ]);
        if (!$update) {
            return response()->json(['status' => false, 'message' => 'Interview Location Not Updated.'], 201);
        }

        return response()->json(['status' => true, 'message' => 'Interview Location Updated.'], 200);
    }

   
    public function destroy($id)
    {
        $joblocation = InterviewLocation::find($id);
        $joblocation->delete();
    }
}
