<?php

namespace App\Http\Controllers\Course;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\MitCourseRegister;
use App\CoursesAptitude;
use App\CoursesCandidateStatus;

class AptitudeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:course-admin');
    }

    public function Data()
    {
        $candidate_data = MitCourseRegister::select('id','mit_code', 'name')->whereHas('addmissionStatus', function( $q ){
            $q->where('course_candidate_status.admission_status', '1')
            ->where('course_candidate_status.aptitude_status', '0');
        })
        ->get();
        // dd($data);
        return view('courses.Aptitude.index', compact('candidate_data'));
    }
    public function storeData(Request $req){
        $i = count($req->checkbox);
        // dd($req->all());
        for ($j = 0; $j < $i; $j++) {
            $aptitude_status = $req->aptitude_status;
            $admsn_id_check = $req->admsn_id_check[$j];

            $reg_code = MitCourseRegister::where('id', $admsn_id_check)->first();
            // dd($reg_code);
            $file_document = 'NULL';
            if($req->file('apt_doc_check')[$j]){
            
                $file_reg_code = str_replace("/", "_", $reg_code->mit_code);
                $file = $req->file('apt_doc_check')[$j];
                $file_document = $file_reg_code.'_aptitude_doc.'.$file->getClientOriginalExtension();
                $file_loc = public_path("documents/course_documents/aptitude/$file_reg_code");
            }
            $apt = new CoursesAptitude();
            $apt->admission_id = $admsn_id_check;
            $apt->marks = $req->marks_check[$j];
            $apt->aptitude_status = $req->aptitude_status;
            $apt->aptitude_doc = $file_document;
            $apt->save();

            CoursesCandidateStatus::where('reg_id',$admsn_id_check)->update(['aptitude_status'=>'1']);

            if($file_document!='NULL'){
                $file->move($file_loc,$file_document);
            }

        }

        return redirect()->back()->with('alert_success','Aptitude Status Added Successfully!');
    }

    public function showAptitudeList(){
        // $candidate = MitCourseRegister::select('id','mit_code', 'name')->whereHas('addmissionStatus', function( $q ){
        //     $q->where('course_candidate_status.admission_status', '1')
        //     ->where('course_candidate_status.aptitude_status', '1');
        // })
        // ->get();
        $candidate_data = CoursesAptitude::all();
        //$candidate_data = CoursesAptitude::with('CandidateData')->get();-------main main main
        
        // $candidate = MitCourseRegister::with('addmissionStatus')->get();
        // return view('admin.aptitude.aptitude_list', compact('candidate_data'));
        


        //dd($candidate);
        
        return view('courses.Aptitude.Aptitude_result_list', compact('candidate_data'));
    }

   
}
