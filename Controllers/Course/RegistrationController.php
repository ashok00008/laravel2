<?php

namespace App\Http\Controllers\Course;

use App\CoursesCentre;
use App\District;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\MitCourseRegister;
use App\CoursesCandidateStatus;
use App\MitCourses;
use App\State;
use Illuminate\Support\Facades\Auth;

class RegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:course-admin');
    }

    public function index(Request $req){
        if(Auth::user('course-admin')->user_role == 'admin'){

            $registrations = MitCourseRegister::when($req->search != null, function($q) use ($req){

                // return $q->whereDate('created_at',$req->name);
                   return $q->where(function($q) use ($req){
                            $q->where('name', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('email', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('shift', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('type_of_course', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('qualification', 'LIKE', '%'.$req->search.'%')
                            ->orWhereHas('monthName', function( $q ) use ( $req ){
                                $q->where('month_name', 'LIKE', '%'.$req->search.'%');
                            })
                            ->orWhereHas('courseName', function( $q ) use ( $req ){
                                $q->where('course_name', 'LIKE', '%'.$req->search.'%');
                            })
                            ->orWhereHas('locationName', function( $q ) use ( $req ){
                                $q->where('location_name', 'LIKE', '%'.$req->search.'%');
                            });
                        });
                        // ->orWhereRelation('monthName','month_name', 'LIKE', '%'.$req->search.'%'); 
            
                })
                ->when($req->date != null, function($q) use ($req){

                    return $q->whereDate('created_on',$req->date);
        
                })
                ->when($req->status != null, function  ($q) use ($req){
    
                    return $q->whereHas('addmissionStatus', function( $q ) use ( $req ){
                        $q->where('course_candidate_status.admission_status', $req->status);
                    });
    
                })
                ->when($req->course != null, function  ($q) use ($req){
                    return $q->where('course_id', $req->course);
                })
                ->orderBy('id','desc')
                ->when($req->pagination != null, function  ($q) use ($req){
    
                    return $q->paginate($req->pagination);
    
                },function ($q) use ($req){

                    return $q->paginate(10);
        
                });
        }
        elseif(Auth::user('course-admin')->user_role == 'online_admin'){

            $registrations = MitCourseRegister::where('type_of_course','Online')->when($req->search != null, function($q) use ($req){

                // return $q->whereDate('created_at',$req->name);
                  return $q->where(function($q) use ($req){
                            $q->where('name', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('email', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('shift', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('type_of_course', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('qualification', 'LIKE', '%'.$req->search.'%')
                            ->orWhereHas('monthName', function( $q ) use ( $req ){
                                $q->where('month_name', 'LIKE', '%'.$req->search.'%');
                            })
                            ->orWhereHas('courseName', function( $q ) use ( $req ){
                                $q->where('course_name', 'LIKE', '%'.$req->search.'%');
                            })
                            ->orWhereHas('locationName', function( $q ) use ( $req ){
                                $q->where('location_name', 'LIKE', '%'.$req->search.'%');
                            });
                        });
                        // ->orWhereRelation('monthName','month_name', 'LIKE', '%'.$req->search.'%'); 
            
                    })
                ->when($req->date != null, function($q) use ($req){

                    return $q->whereDate('created_on',$req->date);
        
                })
                ->when($req->status != null, function  ($q) use ($req){
    
                    return $q->whereHas('addmissionStatus', function( $q ) use ( $req ){
                        $q->where('course_candidate_status.admission_status', $req->status);
                    });
    
                })
                ->when($req->course != null, function  ($q) use ($req){
                    return $q->where('course_id', $req->course);
                })
                ->orderBy('id','desc')
                ->when($req->pagination != null, function  ($q) use ($req){
    
                    return $q->paginate($req->pagination);
    
                },function ($q) use ($req){
                    
                    return $q->paginate(10);
        
                });
        }
        else{

            $registrations = MitCourseRegister::where('location',Auth::user('course-admin')->location)->when($req->search != null, function($q) use ($req){

                // return $q->whereDate('created_at',$req->name);
                  return $q->where(function($q) use ($req){
                            $q->where('name', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('email', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('shift', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('type_of_course', 'LIKE', '%'.$req->search.'%')
                            ->orWhere('qualification', 'LIKE', '%'.$req->search.'%')
                            ->orWhereHas('monthName', function( $q ) use ( $req ){
                                $q->where('month_name', 'LIKE', '%'.$req->search.'%');
                            })
                            ->orWhereHas('courseName', function( $q ) use ( $req ){
                                $q->where('course_name', 'LIKE', '%'.$req->search.'%');
                            })
                            ->orWhereHas('locationName', function( $q ) use ( $req ){
                                $q->where('location_name', 'LIKE', '%'.$req->search.'%');
                            });
                        });
                        // ->orWhereRelation('monthName','month_name', 'LIKE', '%'.$req->search.'%'); 
            
                    })
                ->when($req->date != null, function($q) use ($req){

                    return $q->whereDate('created_on',$req->date);
        
                })
                ->when($req->status != null, function  ($q) use ($req){
    
                    return $q->whereHas('addmissionStatus', function( $q ) use ( $req ){
                        $q->where('course_candidate_status.admission_status', $req->status);
                    });
    
                })
                ->when($req->course != null, function  ($q) use ($req){
                    return $q->where('course_id', $req->course);
                })
                ->orderBy('id','desc')
                ->when($req->pagination != null, function  ($q) use ($req){
    
                    return $q->paginate($req->pagination);
    
                },function ($q) use ($req){
    
                    return $q->paginate(10);
        
                });
        }
        // dd($registrations);
        // $registrations = MitCourseRegister::orderBy('id', 'desc')->paginate(10);
        $courses = MitCourses::all();
        return view('courses.registration.index', compact('registrations', 'courses'));
    }
    
    public function exportCSV(Request $req){

        if ($req->export_all_csv) {
            $fileName = 'export_all_registrations.csv';

            if(Auth::user('course-admin')->user_role == 'admin'){
                $registrations = MitCourseRegister::orderBy('id','desc')
                ->get();
            }
            elseif(Auth::user('course-admin')->user_role == 'online_admin'){
                $registrations = MitCourseRegister::where('type_of_course','Online')->orderBy('id','desc')
                ->get();
            }
            else{
                $registrations = MitCourseRegister::where('location',Auth::user('course-admin')->location)->orderBy('id','desc')
                ->get();
            }

            $headers = array(
                        "Content-type"        => "text/csv",
                        "Content-Disposition" => "attachment; filename=$fileName",
                        "Pragma"              => "no-cache",
                        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                        "Expires"             => "0"
                    );

            $columns = array('S.No.', 'Name', 'Email', 'Contact', 'Qualification', 'Location', 'Course', 'Preferred Month', 'Preferred Shift', 'Admission Status', 'Reference', 'Registered On');
            
            
              
            $callback = function () use ($registrations, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                $sno = 1;
                foreach ($registrations as $task) {
                 
                    if($task->addmissionStatus){
                        if($task->addmissionStatus->admission_status == 0){
                            $admissionStatus = 'Registered';
                        }
                        elseif($task->addmissionStatus->admission_status == 1){
                            $admissionStatus = 'Self Admit';
                        }
                        elseif ($task->addmissionStatus->admission_status == 2){
                            $admissionStatus = 'Admit';
                        }
                        elseif ($task->addmissionStatus->admission_status == 3){
                            $admissionStatus = 'Cancelled';

                        }
                    }else{
                        $admissionStatus = 'Registered';
                    }
                        
                    // $date = Carbon::parse($task->created_on)->format('Y-m-d');
                    // $date = Carbon::parse($task->created_on)->format('Y-m-d');
                    // $date = $task->created_on->toDateString();
                    // $date = new Carbon\Carbon($task->created_on)->toDateString();
                    // $date =  $task->created_on;
                   
             

                    $row['S.No.'] = $sno++;
                    $row['Name']  = $task->name;
                    $row['Email'] = $task->email;
                    $row['Contact'] = $task->contact;
                    $row['Qualification'] = ucfirst($task->qualification);
                    $row['Location'] = $task->location ? $task->locationName->location_name : 'N/A';
                    $row['Course'] = $task->course_id ? $task->courseName->course_name : 'N/A';
                    $row['Preferred Month'] = $task->monthName ? $task->monthName->month_name : 'N/A';
                    $row['Preferred Shift'] = $task->shift ? $task->shift : 'N/A';
                    $row['Admission Status'] = $admissionStatus;
                    $row['Reference'] = $task->reference ? $task->reference : 'N/A';
                    $row['date'] = date('d-m-Y', strtotime($task->created_on));
                   
                    fputcsv($file, array($row['S.No.'], $row['Name'], $row['Email'], $row['Contact'], $row['Qualification'], $row['Location'], $row['Course'], $row['Preferred Month'], $row['Preferred Shift'], $row['Admission Status'], $row['Reference'], $row['date']));
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }
        elseif ($req->export_csv) {
           
            $i = count($req->registrationCheck);
            $ids = implode(",", $req->registrationCheck);
            $fileName = 'registratiosn_list.csv';
            $registrations = MitCourseRegister::whereIn('id', $req->registrationCheck)
                ->orderBy('id','desc')
                ->get();

            $headers = array(
                        "Content-type"        => "text/csv",
                        "Content-Disposition" => "attachment; filename=$fileName",
                        "Pragma"              => "no-cache",
                        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                        "Expires"             => "0"
                    );

            $columns = array('S.No.', 'Name', 'Email', 'Contact', 'Qualification', 'Location', 'Course', 'Preferred Month', 'Preferred Shift', 'Admission Status', 'Reference', 'Registered On');
              
            $callback = function () use ($registrations, $columns) {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                $sno = 1;
                foreach ($registrations as $task) {

                    if($task->addmissionStatus){
                        if($task->addmissionStatus->admission_status == 0){
                            $admissionStatus = 'Registered';
                        }
                        elseif($task->addmissionStatus->admission_status == 1){
                            $admissionStatus = 'Self Admit';
                        }
                        elseif ($task->addmissionStatus->admission_status == 2){
                            $admissionStatus = 'Admit';
                        }
                        elseif ($task->addmissionStatus->admission_status == 3){
                            $admissionStatus = 'Cancelled';

                        }
                    }else{
                        $admissionStatus = 'Registered';
                    }
                        
                  
                    $row['S.No.'] = $sno++;
                    $row['Name']  = $task->name;
                    $row['Email'] = $task->email;
                    $row['Contact'] = $task->contact;
                    $row['Qualification'] = ucfirst($task->qualification);
                    $row['Location'] = $task->location ? $task->locationName->location_name : 'N/A';
                    $row['Course'] = $task->course_id ? $task->courseName->course_name : 'N/A';
                    $row['Preferred Month'] = $task->monthName ? $task->monthName->month_name : 'N/A';
                    $row['Preferred Shift'] = $task->shift ? $task->shift : 'N/A';
                    $row['Admission Status'] = $admissionStatus;
                    $row['Reference'] = $task->reference ? $task->reference : 'N/A';
                    $row['date'] = date('d-m-Y', strtotime($task->created_on));

                   
                    fputcsv($file, array($row['S.No.'], $row['Name'], $row['Email'], $row['Contact'], $row['Qualification'], $row['Location'], $row['Course'], $row['Preferred Month'], $row['Preferred Shift'], $row['Admission Status'], $row['Reference'], $row['date']));
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        }
        return redirect()->back();
    }

    
    public function candidateDetails(Request $request)
    {
        // dd($request->reg_id);
        $state = State::all();
        // $get_doc1_type = Doc1Type::all(); 
        $reg_can = MitCourseRegister::all();
        $centres = CoursesCentre::all();    
        $data = MitCourseRegister::with('getCourseDetails')->where("id",$request->reg_id)->first();
        if($data->getAdmission){
            $district = District::where('id',$data->getAdmission->district)->first();
        }
        else{
            $district = false;
        }
        // dd($data->document);
        return view('courses.admission.viewdetails', compact('centres', 'reg_can', 'state', 'data', 'district'));
    }

    
    public function cancelAdmission(Request $req){
        // dd($req->all());
        $status = CoursesCandidateStatus::where('reg_id',$req->reg_id)->first();
        $status->update([
            'admission_status'=> '3',
            'remarks'=>$req->remarks
        ]);

        return redirect()->back()->with('message','Cancelled');
    }

}
