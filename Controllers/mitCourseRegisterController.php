<?php

namespace App\Http\Controllers;

use App\CoursesAvailable;
use App\CoursesCandidateStatus;
use App\CoursesCentre;
use Illuminate\Http\Request;
use App\MitCourseRegister;
use App\Months;
use App\MitCourses;
use App\CoursesShift;
use App\Qualification;
use App\CoursesLocation;
use Mail;
use Illuminate\Support\Str;

class mitCourseRegisterController extends Controller
{
    public function mitCourseRegistration(){
        $locations = CoursesLocation::where('status', '1')->get();
        $qualifications = Qualification::get();
        $allCourses = MitCourses::where('status','active')->get();
        return response()->json(['locations' => $locations, 'qualifications' => $qualifications, 'allCourses' => $allCourses],200);

    }

    public function postMitCourseRegistration(Request $req){

        $course_name = MitCourses::findOrFail($req->course_id, ['course_name']);
        if($req->location){
            // $month = Months::findOrFail($req->month, ['month_name']);
            $location = CoursesLocation::findOrFail($req->location, ['location_name','short_name','footer_img']);
            $mit_code = "$location->short_name/";
            $cc_location = Str::lower($location->location_name)."@beciljobs.com";
            $subject = "Provisional Registration for $course_name->course_name course | $location->location_name";
            $footer = $location->footer_img;
        }
        else{
            $mit_code = "DL/";
            $cc_location = "delhi@beciljobs.com";
            $subject = "Provisional Registration for $course_name->course_name course";
            $footer = "delhi_footer.png";
        }
        
        //Unique Self Registration Code
        $total_rows = MitCourseRegister::orderBy('id', 'desc')->count();

        if($total_rows==0){
            $mit_code .= '0001';
        }else{
            $last_id = MitCourseRegister::orderBy('id', 'desc')->first()->id;
            $mit_code .= sprintf("%'04d",$last_id + 1);
        }

        $check_exist = MitCourseRegister::where('email', $req->email)->count();
        if($check_exist>0){
            return response()->json(['error' => 'User Already Registered!!'],200);
        }

       $mit = new MitCourseRegister();
       $mit->mit_code = $mit_code;
       $mit->name = $req->name;
       $mit->contact = $req->contact;
       $mit->qualification = $req->qual;
       $mit->email = $req->email;
       $mit->course_id = $req->course_id;
       $mit->location = $req->location;
       $mit->month = $req->month;
       $mit->shift = $req->shift;
       $mit->type_of_doc = $req->type_of_doc;
       $mit->id_proof_no = $req->id_proof_no;
       $mit->type_of_course = $req->course_type;
       $mit->reference = $req->reference;

       if($req->hasFile('id_proof_doc')){
            $file = $req->file('id_proof_doc');
            $ext = $file->getClientOriginalExtension();
            $filename = time() . '.' . $ext;

            $file->move('documents/course_documents/id_proof' , $filename);
            $mit->id_proof_doc = $filename;
        }
        
        if($req->reg_date){
            $mit->created_on = $req->reg_date;
            $mit->save(['timestamps' => false]);
        }
        else{
            $mit->save();
        }

        $status = new CoursesCandidateStatus();
        $status->reg_id = $mit->id;
        $status->admission_status = '0';
        $status->save();

    //    $toEmail = $req->email;
       $toEmail = "bishtsonu251011@gmail.com";
        $from = 'info@prakharsoftwares.com';

       $shift = $req->shift;
        
    //    $data = array('reg_code'=>$mit_code, 'name'=>$req->name, 'course_name'=>$course_name->course_name, 'location'=>$location->location_name, 'footer'=>$location->footer_img, 'month'=>$month->month_name, 'shift'=>$shift);
        $data = array('reg_code'=>$mit_code, 'name'=>$req->name, 'course_name'=>$course_name->course_name, 'footer'=>$footer);
    
       Mail::send('SendMail.certificate-course', $data, function($message) use ($toEmail,$from,$subject,$cc_location) {
        $message->to($toEmail, 'Certificate Course')
        ->cc(['bishtsonu25108@gmail.com', $cc_location])
        ->subject($subject);
        $message->from($from, env('APP_NAME'));
     });

       return response()->json(['data' => 'success','name' => $req->name,'course_name' => $course_name->course_name, 'mit_code' => $mit_code],200);
    //    return redirect()->back()->with('alert_success','Your have been registered successfully. We will contact you very soon!');
    }

    public function mitCourseRegistrationList(){
        $mit_data = MitCourseRegister::with("getCourseDetails")->orderByDesc("id")->get(); 
        return response()->json(['data' => $mit_data], 200);
    }
    public function mitCourseDetails(Request $req){
        $course = MitCourses::where([
            ['status','=','active'],
            ['id','=',$req->id]
            ])->get();
        return response()->json(['course' => $course],200);
    }

    public function fetchMonth(Request $req){
        // $months = CoursesAvailable::with('getMonths')->where([
        //     ['course_id', '=', $req->course_id],
        //     ['location_id', '=', $req->location]                                                             
        // ])->get();
        $months = Months::all();
        return response()->json(['months' => $months]);
    }
    
    public function fetchShift(Request $req){
        $shift = CoursesAvailable::with('getShift')->where([
            ['course_id', '=', $req->course_id],
            ['location_id', '=', $req->location],
            ['month_id', '=', $req->month]
        ])->get();
        return response()->json(['shift' => $shift]);
    }
    
    public function fetchCourse(Request $req){
        $courses = CoursesAvailable::with('getCourses:id,course_name')->distinct('course_id')->where('location_id', $req->location)->select('course_id')->get();
        return response()->json(['courses' => $courses]);
    }

    public function getAllCourse(){
        $courses = MitCourses::get();
        return response()->json(['courses' => $courses]);
    }

    
    public function fetchCentres(Request $req){
        $centres = CoursesCentre::where('location_id', $req->loc_id)->get();
        return response()->json(['courses' => $centres]);
    }

}