<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Jobs;
use App\Advertisement;
use App\InstructionAdmitCard;

class AdmitCardInstructionController extends Controller
{
    public function index(){
        $data = DB::table('instruction_admit_cards')
        ->get();
        return response()->json($data);
    }
    public function getSpecialInstruction(){
        $data = DB::table('jobs')
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
        ->select('jobs.id','special_instruction_admitcard','advertisements.advertisement_no','designations.designation','jobs.updated_at')
        ->where('exam_conducted','Yes')
        ->get();
        return response()->json($data);  
    }
    public function getAdvertisementHasExamConducted(){
        $data = DB::table('jobs')
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->where('jobs.exam_conducted','Yes')
        ->select('advertisements.id','advertisements.advertisement_no')
        ->distinct()
        ->get();
        return response()->json([
            'data'=>$data
        ],200);
    }
    public function getPositionByAdvertisement(Request $request){
        $advertisement_id = $request->advertisement_id;

        $designations = DB::table('jobs')
            ->leftJoin('designations', 'designations.id', 'jobs.designation_id')
            ->select('designations.id', 'designations.designation')
            ->where('jobs.adv_id', $advertisement_id)
            ->distinct()
            ->get();

        return response()->json(['status' => 'success', 'data' => $designations], 200);
    }
    public function addSpecialInstruction(Request $request){
        $ads_id = $request->params['adv_id'];
        $designation_id = $request->params['position_id'];
        $ed_data = $request->params['editor_data'];
        
        $jobs_admit = Jobs::where(['adv_id' => $ads_id, 'designation_id' => $designation_id])->update([
            'special_instruction_admitcard' => $ed_data
        ]);

        if (!$jobs_admit) {
            return response()->json(['status' => false, 'message' => 'Instruction Not Updated.'], 201);
        }

        return response()->json(['status' => true, 'message' => 'Instruction Updated.'], 200);
    }
    public function addGeneralInstruction(Request $request){
        
        $ed_data = $request->params['editor_data'];
        
        $jobs_admit = new InstructionAdmitCard();
        $jobs_admit->general_instruction = $ed_data;
        $jobs_admit->save();

        if (!$jobs_admit) {
            return response()->json(['status' => false, 'message' => 'Instruction Not Inserted.'], 201);
        }

        return response()->json(['status' => true, 'message' => 'Instruction Added.'], 200);
    }
    public function destroy($id) {
        $fee = InstructionAdmitCard::find($id);
        $fee->delete();
    }
    public function getData($id) {
        $general = InstructionAdmitCard::where('id',$id)->first();
        return response()->json([
            'data'=>$general
        ],200);
    }
    public function updateGeneralInstruction(Request $request){
        $ed_id = $request->params['editor_id'];
        $ed_data = $request->params['editor_data'];

        $jobs_admit = InstructionAdmitCard::where('id',$ed_id)->update([
            'general_instruction' => $ed_data
        ]);

        if (!$jobs_admit) {
            return response()->json(['status' => false, 'message' => 'Instruction Not Updated.'], 201);
        }

        return response()->json(['status' => true, 'message' => 'Instruction Updated.'], 200);
    }


public function countRow(){
        $count = DB::table('instruction_admit_cards')
        ->count();
        return response()->json($count);
    }
    public function getSplAdmitData($id){
        $data = DB::table('jobs')
        ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
        ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
        ->select('jobs.id','special_instruction_admitcard','advertisements.advertisement_no','designations.designation','jobs.updated_at')
        ->where('jobs.id',$id)
        ->get();
        return response()->json(['data'=>$data]); 

    }
    public function updateSpecialInstruction(Request $request){
        $job_id = $request->params['job_id'];
        $ed_data = $request->params['editor_data'];

        $jobs_admit = Jobs::where('id',$job_id)->update([
            'special_instruction_admitcard' => $ed_data
        ]);

        if (!$jobs_admit) {
            return response()->json(['status' => false, 'message' => 'Instruction Not Updated.'], 201);
        }

        return response()->json(['status' => true, 'message' => 'Instruction Updated.'], 200); 
    }
    public function delete($id){
        Jobs::where('id',$id)->update(['special_instruction_admitcard' => '']);

    }
}
