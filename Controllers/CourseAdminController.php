<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Admin;
use App\CoursesCandidateAdmission;
use App\CoursesCandidateDocument;
use App\CoursesCandidateEducation;
use App\CoursesCandidateStatus;
use App\CoursesCentre;
use App\District;
use App\MitCourseRegister;
use App\State;
use Illuminate\Support\Facades\Hash;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CourseAdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:course-admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $todayDate = Carbon::now()->format('Y-m-d');

        if(Auth::user('course-admin')->user_role == 'admin'){

            $todayRegistrations = MitCourseRegister::whereDate('created_on',$todayDate)->count();

            $todayJaipurRegistrations = MitCourseRegister::where('location', '1')->whereDate('created_on',$todayDate)->count();
            $todayBhopalRegistrations = MitCourseRegister::where('location', '2')->whereDate('created_on',$todayDate)->count();
            $todayDodiRegistrations = MitCourseRegister::where('location', '3')->whereDate('created_on',$todayDate)->count();
    
            $todayOnlineRegistrations = MitCourseRegister::where('type_of_course', 'Online')->whereDate('created_on',$todayDate)->count();
            $todayOfflineRegistrations = MitCourseRegister::where('type_of_course', 'Offline')->whereDate('created_on',$todayDate)->count();
    
    
            $totalRegistrations = MitCourseRegister::count();
    
            $totalJaipurRegistrations = MitCourseRegister::where('location', '1')->count();
            $totalBhopalRegistrations = MitCourseRegister::where('location', '2')->count();
            $totalDodiRegistrations = MitCourseRegister::where('location', '3')->count();
    
            $totalOnlineRegistrations = MitCourseRegister::where('type_of_course', 'Online')->count();
            $totalOfflineRegistrations = MitCourseRegister::where('type_of_course', 'Offline')->count();
    
    
            if($todayJaipurRegistrations > 0 || $todayBhopalRegistrations > 0 || $todayDodiRegistrations > 0 || $todayOnlineRegistrations > 0){
                        $result = "['Working','Hours'],['Jaipur',". (int)$todayJaipurRegistrations."],['Bhopal',". (int)$todayBhopalRegistrations."],['Dodi',". (int)$todayDodiRegistrations."],['Online',". (int)$todayOnlineRegistrations."]";
            } else{
                $result = null;
            }
        
            $resultTotal = "['Working','Hours'],['Jaipur',". (int)$totalJaipurRegistrations."],['Bhopal',". (int)$totalBhopalRegistrations."],['Dodi',". (int)$totalDodiRegistrations."],['Online',". (int)$totalOnlineRegistrations."]";
                       
            
            return view('courses.dashboard.admin', compact('resultTotal','result','todayRegistrations','todayJaipurRegistrations','todayBhopalRegistrations','todayDodiRegistrations','todayOnlineRegistrations', 'todayOfflineRegistrations','totalRegistrations','totalJaipurRegistrations','totalBhopalRegistrations','totalDodiRegistrations','totalOnlineRegistrations','totalOfflineRegistrations'));

        }

        if(Auth::user('course-admin')->user_role == 'jaipur_admin'){

            $todayJaipurRegistrations = MitCourseRegister::where('location', '1')->whereDate('created_on',$todayDate)->count();
    
            $totalJaipurRegistrations = MitCourseRegister::where('location', '1')->where('location', '1')->count();
    
    
            return view('courses.dashboard.jaipurAdmin', compact('todayJaipurRegistrations','totalJaipurRegistrations'));

        }

        if(Auth::user('course-admin')->user_role == 'online_admin'){

            $todayOnlineRegistrations = MitCourseRegister::where('type_of_course', 'Online')->whereDate('created_on',$todayDate)->count();
    
            $totalOnlineRegistrations = MitCourseRegister::where('type_of_course', 'Online')->count();
    
    
    
            return view('courses.dashboard.onlineAdmin', compact('todayOnlineRegistrations','totalOnlineRegistrations'));

        }
       
    }

    public function CourseHome()
    {
        return view('courses.courseadmindash');
    }
    public function updatePassword(Request $request){
		
        $this->validate($request, [
            'newpassword' => 'min:6|required_with:confirmpassword|same:confirmpassword|regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[@!$#%]).*$/',
            'confirmpassword' => 'min:6'
        ]);

        $id = Auth::user()->id;
        $email = Auth::user()->email;
        $currentpassword = $request->oldpassword;

        $getpassword = Admin::find($id)->password;

        if (Hash::check($currentpassword, $getpassword)) {
            $change = Admin::find($id);
            $change->password = Hash::make($request->confirmpassword);
           $dd = $change->save();
            
            if (!$dd) {
                return response()->json(['error' => 'Something went wrong'], 200);
            }
            
            $this->logout();
            return response()->json(['success' => 'Password Changed'], 200);
        
        } else {
            return response()->json(['warning' => 'Old Password Not Matched'], 201);
        }
    }
    public function logout()
    {
		Auth::guard('course-admin')->logout();
		//return redirect()->route('/admin');
        return redirect('/course-admin');
	}

    
    public function admissionForm(Request $request)
    {
        $state = State::all();
        $reg_can = MitCourseRegister::all();
        $centres = CoursesCentre::all();    
        $data = MitCourseRegister::with('getCourseDetails')->where("id",$request->reg_id)->first();
        // dd($centres);
        return view('courses.admission.admission', compact('centres', 'reg_can', 'state', 'data'));
    }

    public function fetchRegData(Request $request)
    {
        $data = MitCourseRegister::with('getCourseDetails')->where("id",$request->reg_id)->first();
        return response()->json($data);
    }
    

    public function store(Request $req)
    { 
        $this->validate($req, [
            'name' => 'required',
            'mother_name' => 'required',
            'dob' => 'required',
            'qualification' => 'required',
        ]);

        MitCourseRegister::where('id', $req->reg_id)
        ->update([
            'name' => $req->name,
            'contact' => $req->contact,
            'qualification' => $req->qualification,
            'email' => $req->email
            // 'course_id' => $req->course_id
        ]);

         $student = new CoursesCandidateAdmission();
         $student->centre_id = $req->centre_id;
         $student->register_id = $req->reg_id;
         $student->father_name = $req->father_name;
         $student->mother_name = $req->mother_name;
         $student->gender = $req->gender;
         $student->dob = $req->dob;
         $student->aadhar_no = $req->identity_no;
         $student->state = $req->state_id_add;
         $student->district = $req->district_id_add;
         $student->pincde = $req->pincode_add;
         $student->address = $req->address;
         $student->category = $req->category;
         $student->fb_user_id = $req->facebook_user;
         $student->linkedin_user_id = $req->linkedin_user;
         $student->chronic_disease = $req->chronic_disease;
         $student->covid_type = $req->covid_dose;
         $student->marital_status = $req->marital_status;
         $student->current_status = $req->current_status;
         $student->added_by = Auth::user()->id; 
         $student->save();

         
         $educ = new CoursesCandidateEducation();
         $educ->register_id = $req->reg_id;
         $educ->board = $req->can_board;
         $educ->year_of_passing = $req->can_yop;
         $educ->percentage = $req->can_per;
         $educ->tech_qualification = $req->tech_qualification;
         $educ->prev_skill_training = $req->prev_skill_tra;
         $educ->work_experience = $req->can_work_exp;
         $educ->save();


        //  dd("submit");

         //Work in progress

         if($req->hasFile('user-image')){
            $uploadPath = 'documents/course_documents/user-image/';
                $img = $req->file('user-image');
                $extension = $img->getClientOriginalExtension();
                $filename = time().'.' . $extension;
                // $finalImagePathName = $uploadPath . $filename;
                // $reg_doc->register_id = $req->reg_id;
                $reg_doc = new CoursesCandidateDocument();         
                $reg_doc->reg_id = $req->reg_id;
                $reg_doc->doc_type = "user-image";
                $reg_doc->doc_file = $filename;      
                $reg_doc->save();    
                
                $img->move($uploadPath, $filename);
            }
            
               
        if($req->hasFile('category_doc')){
            $uploadPath = 'documents/course_documents/category-doc/';
                $img = $req->file('category_doc');
                $extension = $img->getClientOriginalExtension();
                $filename = time().'.' . $extension;
                // $finalImagePathName = $uploadPath . $filename;
                // $reg_doc->register_id = $req->reg_id;
                $reg_doc = new CoursesCandidateDocument();         
                $reg_doc->reg_id = $req->reg_id;
                $reg_doc->doc_type = "category-doc";
                $reg_doc->doc_file = $filename;      
                $reg_doc->save();    
                
                $img->move($uploadPath, $filename);
            }
          
        if($req->hasFile('covid_doc')){
            $uploadPath = 'documents/course_documents/covid-doc/';
            $img = $req->file('covid_doc');
            $extension = $img->getClientOriginalExtension();
            $filename = time().'.' . $extension;
            // $finalImagePathName = $uploadPath . $filename;
            // $reg_doc->register_id = $req->reg_id;
            $reg_doc = new CoursesCandidateDocument();         
            $reg_doc->reg_id = $req->reg_id;
            $reg_doc->doc_type = "covid-doc";
            $reg_doc->doc_file = $filename;      
            $reg_doc->save();    
            
            $img->move($uploadPath, $filename);
        }

        
        if($req->aadhar_id){
            $reg_doc = new CoursesCandidateDocument();         
            $reg_doc->reg_id = $req->reg_id;
            $reg_doc->doc_type = "aadhar-doc";
            $reg_doc->doc_file = $req->aadhar_id;      
            $reg_doc->save();    
        }
        else if($req->hasFile('aadhar_doc')){
            $uploadPath = 'documents/course_documents/id_proof/';
            $img = $req->file('aadhar_doc');
            $extension = $img->getClientOriginalExtension();
            $filename = time().'.' . $extension;
            // $finalImagePathName = $uploadPath . $filename;
            // $reg_doc->register_id = $req->reg_id;
            $reg_doc = new CoursesCandidateDocument();         
            $reg_doc->reg_id = $req->reg_id;
            $reg_doc->doc_type = "aadhar-doc";
            $reg_doc->doc_file = $filename;      
            $reg_doc->save();    
            
            $img->move($uploadPath, $filename);
        }
        
        if($req->hasFile('education_doc')){
            $uploadPath = 'documents/course_documents/education-document/';
            $img = $req->file('education_doc');
            $extension = $img->getClientOriginalExtension();
            $filename = time().'.' . $extension;
            // $finalImagePathName = $uploadPath . $filename;
            // $reg_doc->register_id = $req->reg_id;
            $reg_doc = new CoursesCandidateDocument();         
            $reg_doc->reg_id = $req->reg_id;
            $reg_doc->doc_type = "education-document";
            $reg_doc->doc_file = $filename;      
            $reg_doc->save();    
            
            $img->move($uploadPath, $filename);
        }
        
        if($req->hasFile('signature_doc')){
            $uploadPath = 'documents/course_documents/signature-doc/';
            $img = $req->file('signature_doc');
            $extension = $img->getClientOriginalExtension();
            $filename = time().'.' . $extension;
            // $finalImagePathName = $uploadPath . $filename;
            // $reg_doc->register_id = $req->reg_id;
            $reg_doc = new CoursesCandidateDocument();         
            $reg_doc->reg_id = $req->reg_id;
            $reg_doc->doc_type = "signature-doc";
            $reg_doc->doc_file = $filename;      
            $reg_doc->save();    
            
            $img->move($uploadPath, $filename);
        }
        

        if($req->hasFile('parent_consent_doc')){
            $uploadPath = 'documents/course_documents/parent-consent/';
            $img = $req->file('parent_consent_doc');
            $extension = $img->getClientOriginalExtension();
            $filename = time().'.' . $extension;
            // $finalImagePathName = $uploadPath . $filename;
            // $reg_doc->register_id = $req->reg_id;
            $reg_doc = new CoursesCandidateDocument();         
            $reg_doc->reg_id = $req->reg_id;
            $reg_doc->doc_type = "parent-consent";
            $reg_doc->doc_file = $filename;      
            $reg_doc->save();    
            
            $img->move($uploadPath, $filename);
        }
        
        $status = CoursesCandidateStatus::updateOrCreate(
            ['reg_id' => $req->reg_id],
            ['admission_status' => '2']
        );
        // $status->reg_id = $req->reg_id;
        // $status->admission_status = '2';
        // $status->save();
        
        return redirect('/course-admin/registration')->with('alert_status','Admission done Successfully!');
    }

    public function downloadTotal(Request $req){
        $from_where = $to_where = '';
        $where = "";
        if($req->from_date != null){
        $where = "where";
        }
        if($req->from_date != null){
            $from_where = "date(created_on) >= '$req->from_date'";
        }
        if($req->from_date != null && $req->to_date != null){
            $to_where = " AND date(created_on) <= '$req->to_date'";
        }
        elseif($req->to_date != null){
            $to_where = " date(created_on) <= '$req->to_date'";
        }

        $data = DB::select(DB::raw("SELECT mit.course_name, b.course_id, b.Jaipur, b.Bhopal, b.Dodi, b.Online FROM mit_courses as mit LEFT join (SELECT reg.course_id,COUNT(if(reg.location='1',1,NULL)) as Jaipur, COUNT(if(reg.location='2',1,NULL)) as Bhopal, COUNT(if(reg.location='3',1,NULL)) as Dodi, COUNT(if(reg.type_of_course='Online',1,NULL)) as Online FROM mit_course_registers as reg $where $from_where $to_where  GROUP BY reg.course_id ORDER BY reg.course_id) as b on b.course_id = mit.id;"));

        $first_date = MitCourseRegister::first(['created_on']);

        return view('courses.Print.total_count', compact('data', 'first_date'));
    }
    

}
