<?php

namespace App\Http\Controllers\Course\Self;

use Dompdf\Options;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MitCourseRegister;
use App\CoursesCandidateAdmission;
use App\CoursesCandidateEducation;
use App\CoursesCandidateStatus;
use App\CoursesCandidateDocument;
use App\CoursesCentre;
use App\District;
use Auth;
use Mail;
use Dompdf\Dompdf;


use App\State;
// use Barryvdh\DomPDF\PDF;
// use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use PDF;

// use barryvdh\DomPDF\Facade\Pdf;

class AdmissionController extends Controller
{
    
    public function sendmailtocandidate(Request $request)
    {
        $reg_id = Crypt::encryptString($request->id);
        $registerDetail = MitCourseRegister::where('id',$request->id)->first();
        

        
        // $toEmail = $registerDetail->email;
        $toEmail = 'bishtsonu25108@gmail.com';
        $from = 'info@prakharsoftwares.com';
        // $cc_location = Str::lower($location->location_name)."@beciljobs.com";
        // dd($from);

        // $shift = $req->shift;
        $subject = "Certification Course Admission Form";
        // 'location'=>$registerDetail->locationName->location_name
        $data = array('reg_id'=>$reg_id,'reg_code'=>$registerDetail->mit_code, 'name'=>$registerDetail->name, 'footer'=>$registerDetail->locationName ? $registerDetail->locationName->footer_img : "delhi_footer.png");
        // dd($data);

        Mail::send('SendMail.certificate-course-admission-form-by-candidate', $data, function($message) use ($toEmail,$from,$subject) {
                $message->to($toEmail, 'Certificate Course')
                // ->cc(['bishtsonu25108@gmail.com', $cc_location])
                ->subject($subject);
                $message->from($from, env('APP_NAME'));
             });

        return redirect()->back()->with('alert_status', 'Mail Sended Successfully');
    }
    
    public function fetchDistrict(Request $request)
    {
        $data = District::where("state_id",$request->state_id)->get(["district_name", "id"]);
        return response()->json($data);
    }

    public function create($id){
        $reg_id = Crypt::decryptString($id);
        // $reg_id = 94;
        // dd($reg_id);

        if(MitCourseRegister::where('id',$reg_id)->exists()){
            if (!CoursesCandidateAdmission::where('register_id', $reg_id)->exists()) {
                $state = State::all();
                $centres = CoursesCentre::all();    
                $registerDetail = MitCourseRegister::where('id', $reg_id)->first();
                // dd($registerDetail->centreName);
                return view('courses.admission.Self.admission', compact('registerDetail', 'state', 'centres'));
            }else{
                Session::flash('invalid', 'User Admission Form Already Completed');
                return view('courses.admission.Self.admission');   
            }    
        }else{
            Session::flash('invalid', 'Invalid Registration');
            return view('courses.admission.Self.admission')->with('message','Invalid Registration');   
        }    
    }


    public function store(Request $req){
        if(MitCourseRegister::where('id',$req->reg_id)->exists()){
            if(!CoursesCandidateAdmission::where('register_id',$req->reg_id)->exists()){

                // $this->validate($req, [
                //     'name' => 'required',
                //     'mother_name' => 'required',
                //     'dob' => 'required',
                //     'qualification' => 'required',
                // ]);
        
                $registerDetail = MitCourseRegister::where('id',$req->id)->first();
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
                elseif($req->hasFile('aadhar_doc')){
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
                    $reg_doc = new CoursesCandidateDocument();         
                    $reg_doc->reg_id = $req->reg_id;
                    $reg_doc->doc_type = "signature-doc";
                    $reg_doc->doc_file = $filename;      
                    $reg_doc->save();    
                    
                    $img->move($uploadPath, $filename);
                }
                
                
                $status = CoursesCandidateStatus::updateOrCreate(
                    ['reg_id' => $req->reg_id],
                    ['admission_status' => '1']
                );
                // $status = new CoursesCandidateStatus();
                // $status->reg_id = $req->reg_id;
                // $status->admission_status = '1';
                // $status->save();
                
                
                // return view('courses.admission.Self.thank-you');
                $detail = MitCourseRegister::where('id',$req->reg_id)->first();

                $pdf = PDF::loadView('courses.admission.preview', ['detail'=>$detail]);
                return $pdf->download('admission-details.pdf');
            
            }else{
                return redirect()->back()->with('already', 'Admission is already done!');
            }
            
        }else{

                return view()->with('invalid', 'Invalid Registration!');
         }     
      
    }

    public function preview($reg_id){
    
        // $detail = MitCourseRegister::with('document','centre','locationName','getAdmission')->where('id',$reg_id)->first()->toArray();
        $detail = MitCourseRegister::where('id',$reg_id)->first();
        // var_dump($detail);
        // exit;
        // $data = (array) $detail;
        // dd($detail);
        // return view('courses.admission.preview', compact('detail'));
        // return Pdf::loadFile(view('courses.admission.preview',  compact('detail')))->save('/file.pdf')->stream('download.pdf');
        // instantiate and use the dompdf class
        // $options = new Options();
        // $options->set('isRemoteEnabled',true);  
        $pdf = PDF::loadView('courses.admission.preview', compact('detail'));
        return $pdf->download('admission-details.pdf'); 

        // $dompdf = new Dompdf();
        // $dompdf->loadHtml(view('courses.admission.preview',  compact('detail')));

        // (Optional) Setup the paper size and orientation
        // $dompdf->setPaper('A4', 'landscape');

        // Render the HTML as PDF
        // $dompdf->render();
        // ob_end_clean();
        // Output the generated PDF to Browser
        // $dompdf->stream();
        return view('courses.admission.preview', compact('detail'));

    }
}
