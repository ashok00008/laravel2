<?php

namespace App\Http\Controllers\Course;

use App\CoursesBatchLessonPlanner;
use App\CoursesCentre;
use App\District;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CoursesLocation;
use App\CoursesBatch;
use App\MitCourseRegister;
use App\MitCourses;
use App\State;
use Illuminate\Support\Facades\Auth;

class BatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:course-admin');
    }


    public function index()
    {
        $courses_locations = CoursesLocation::all();
        // $project = Project::where('id',$project_id->project_id)->first();
        // $centre = CentreDetails::where('project_id',$project_id->project_id )->get();       
        return view('courses.Batch.index', compact("courses_locations"));
    }


    public function createBatch(Request $req)
    {
        $this->validate($req, [
            't_name' => 'required',
            'duration_per_day' => 'required',
            'b_start_date' => 'required',
            'ex_b_end_date' => 'required',
            'b_size' => 'required',
            'lesson_planner' => 'required',
        ]);

        //batch Code
        $total_rows = CoursesBatch::orderBy('id', 'desc')->count();
        $batch_code = "Batch/";
        if ($total_rows == 0) {
            $batch_code .= '0001';
        } else {
            $last_id = CoursesBatch::orderBy('id', 'desc')->first()->id;
            $batch_code .= sprintf("%'04d", $last_id + 1);
        }

        $batch = new CoursesBatch();
        $batch->centre_id = $req->centre_id;
        $batch->batch_code = $batch_code;
        // $batch->trainer_id = $req->t_name;
        $batch->nature_of_training = $req->nature_of_training;

        $batch->batch_start_date = $req->b_start_date;
        $batch->expec_batch_end_date = $req->ex_b_end_date;

        $batch->duration_per_day = $req->duration_per_day;
        $batch->lesson_planner = $req->lesson_planner;
        $batch->ojt_given = $req->ojt_given;
        $req->o_start_date ? $batch->ojt_start_date = $req->ojt_start_date : $batch->ojt_start_date = 'NULL';
        $req->ojt_days ? $batch->ojt_days = $req->ojt_days : $batch->ojt_days = NULL;
        $batch->added_by = Auth::user()->id;
        $batch->save();
        $insertedId = $batch->id;

        //Batch or Section lesson planner
        //Add Trainer Qualification
        if (!empty($req->theory_class[0]) && !empty($req->it_lab_no[0]) && !empty($req->it_lab_no[0])) {
            $i = count($req->theory_class);
            for ($j = 0; $j < $i; $j++) {
                $lesson_planner = new CoursesBatchLessonPlanner();
                $lesson_planner->batch_id = $insertedId;
                $lesson_planner->class_type = $req->lesson_planner;
                $lesson_planner->theory_classroom_no = $req->theory_class[$j];
                $lesson_planner->it_lab_no = $req->it_lab[$j];
                $lesson_planner->practical_lab_no = $req->practical_lab[$j];
                $lesson_planner->theory_cum_practical_lab_no = $req->theory_cum_class[$j];
                $lesson_planner->theory_cum_it_lab_no = $req->it_cum_lab[$j];
                $lesson_planner->it_cum_practical_lab_no = $req->practical_cum_lab[$j];
                $lesson_planner->added_by = Auth::user()->id;
                $lesson_planner->save();
            }
        }

        return redirect()->route('batch_list')->with('alert_success', 'Batch Created Successfully!');
    }

    public function list()
    {
        $batch_data = CoursesBatch::get();
        return view('courses.Batch.list', compact("batch_data"));
    }

    public function allotment()
    {
        $courses_locations = CoursesLocation::where('status', '1')->get();
        return view('courses.Batch.allotment', compact("courses_locations"));
    }

    public function postBatchAllotment(Request $req)
    {
        $this->validate($req, []);

        if (isset($req->admi_reg_id)) {

            // $can_count = count($req->admi_reg_id);    
            //     for($i = 0; $i < $can_count; $i++){
            //         $id = explode(',',$req->admi_reg_id[$i]);
            //         for($j = 0; $j<2; $j++){
            //             $add_id = $id[0];
            //             $reg_id = $id[1];
            //         }
            //             $batchAllotment = new BatchAllotment();
            //             $batchAllotment->centre_id = $req->centre_id;
            //             $batchAllotment->batch_id = $req->batch_id;
            //             $batchAllotment->admission_id = $add_id;
            //             $batchAllotment->register_id = $reg_id;
            //             $batchAllotment->added_by = Auth::user()->id;  
            //             $batchAllotment->save();

            //             $admission = Admission::find($add_id);              
            //             $admission->batch_enroll_status = "enroll"; 
            //             $admission->save();

            //     }
            return redirect()->route('batch_allotment')->with('alert_success', 'Batch Assigned Successfully!');
        } else {
            return redirect()->route('batch_allotment')->with('alert_success', 'Please Select Candidate !');
        }
    }

    public function canListInBatch(Request $req)
    {
        $candidate_data = BatchAllotment::with('allotedCandidateList')->where('batch_id', $req->id)->where('added_by', Auth::user()->id)->get();
        return view('admin.create_batch.show_candidate_list', compact('candidate_data'));
    }

    public function fetchBatchByCentre(Request $req)
    {
        $data = CoursesBatch::where("centre_id", $req->centre_id)->get(["id", "batch_code"]);
        return response()->json($data);
    }

    public function fetchCanForAllot(Request $request)
    {
        $data = MitCourseRegister::whereHas('addmissionStatus', function( $q ) use ( $request ){
            $q->where('course_candidate_status.admission_status', '1');
        })
        ->whereHas('getAdmission', function( $q ) use ( $request ){
            $q->where('course_candidate_admission.centre_id', $request->centre_id);
        })
        ->get();
        dd($data);
        // $data = MitCourseRegister::where("centre_id", $request->centre_id)->where("batch_enroll_status", "unenroll")->with('registrationCode')->get();
        return response()->json($data);
    }
}
