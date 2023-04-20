<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\User;
use App\UserAddress;
use App\UsersDetails;
use App\UsersQualification;
use App\UserRef;
use App\UserExp;
use App\UserDocument;
use App\UserEduDocument;
use DB;
use Auth;
use Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Response;  


class UserProfileController extends Controller
{

    /*public function __construct()
    {
        $this->middleware('auth');
        
    }*/
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function index()
    {
        

       $userid = Auth::user()->id;
            
        $data = DB::table('users') 
            ->leftjoin('users_details', 'users_details.user_id', '=', 'users.id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', '=', 'users.id')
            ->leftjoin('user_exps', 'user_exps.user_id', '=', 'users.id')
            ->leftjoin('users_qualifications', 'users_qualifications.user_id', '=', 'users.id')
            ->leftjoin('user_documents', 'user_documents.user_id', '=', 'users.id')
            ->leftjoin('user_edu_documents', 'user_edu_documents.user_id', '=', 'users.id')
            ->leftjoin('blood_groups', 'blood_groups.id', '=', 'users_details.blood_group_id')
            ->leftjoin('categories', 'categories.id', '=', 'users_details.category_id')
            ->leftjoin('religions', 'religions.id', '=', 'users_details.religion_id')
            ->leftjoin('languages', 'languages.id', '=', 'users_details.language_id')
            ->leftjoin('cities', 'cities.id', '=', 'user_addresses.p_city_id' )
            ->leftjoin('cities as c_cities', 'c_cities.id', '=', 'user_addresses.c_city_id')
            ->leftjoin('states', 'states.id', '=', 'user_addresses.p_state_id')
            ->leftjoin('states as c_states', 'c_states.id', '=', 'user_addresses.c_state_id')
            ->select('users.id','users.fname','users.mname', 'users.lname', 'users.mobile','users.aadhar_no' ,'users.registration_id', 'users_details.email1', 'users_details.email2', 'users_details.mobile1','users_details.father_name','users_details.father_contact','users_details.gender','users_details.pan_no','users_details.dob','users_details.passport_no','categories.cat_name','religions.religion_name','blood_groups.blood_group','languages.language','users_details.prefered_location1','users_details.prefered_location2','user_addresses.p_first_add','user_addresses.p_second_add','user_addresses.p_landmark','user_addresses.p_pincode','cities.name as p_city','states.name as p_state','user_addresses.c_first_add','user_addresses.c_second_add','user_addresses.c_landmark','user_addresses.c_pincode','c_cities.name as c_city_name','c_states.name as c_state_name','user_exps.latest_emp_cname','user_exps.latest_emp_from','user_exps.latest_emp_to','user_exps.prev_emp_cname','user_exps.prev_emp_from','user_exps.prev_emp_to','user_exps.total_exp_year','user_exps.total_exp_month','user_exps.relevant_exp_year','user_exps.relevant_exp_month','user_exps.current_salary_monthly','user_exps.home_salary_as_bank','users_qualifications.eight_school_name','users_qualifications.eight_passing_year','users_qualifications.eight_marks','users_qualifications.ten_board_name','users_qualifications.ten_passing_year','users_qualifications.ten_marks','users_qualifications.ten_stream','users_qualifications.twelve_board_name','users_qualifications.twelve_passing_year','users_qualifications.twelve_marks','users_qualifications.twelve_stream','users_qualifications.diploma_institute_name','users_qualifications.diploma_name','users_qualifications.diploma_passing_year','users_qualifications.diploma_marks','users_qualifications.diploma_stream','users_qualifications.ug_degree','users_qualifications.ug_branch','users_qualifications.ug_university','users_qualifications.ug_year','users_qualifications.ug_marks','users_qualifications.pg_degree','users_qualifications.pg_branch','users_qualifications.pg_university','users_qualifications.pg_year','users_qualifications.pg_marks','users_qualifications.pg_degree','users_qualifications.additional_institute_name','users_qualifications.additional_qual','users_qualifications.additional_qual_marks','users_qualifications.additional_qual_year','user_documents.photograph','user_documents.signature','user_documents.resume','user_documents.caste_certificate','user_edu_documents.ten_doc','user_edu_documents.twelve_doc','user_edu_documents.diploma_doc','user_edu_documents.ug_doc','user_edu_documents.pg_doc','user_edu_documents.add_cert_doc')
            ->where('users.id',$userid)
            ->first();
             $myArray = json_decode(json_encode($data), true);
             $dec_aadhar = decrypt($myArray['aadhar_no']);
             $data->dec_aadhar = $dec_aadhar;
              
             return response()->json([
            'data'=>$data
            ],200);
    }


    public function getsession()
    {

        $data = Auth::user()->id;
        

        return response()->json([
            'data'=>$data
            ],200);

    }

    public function getStages()
    {

        $uid = Auth::user()->id;
         $getStages =User::select('id','stage')->where('id',$uid)->first();
        

        return response()->json([
            'data'=>$getStages
            ],200);

    }

    public function checklogin()
    {
        if(isset(Auth::user()->id))
        {
        $data = Auth::user()->id;
        }
        else{
          $data = "Notlogin";  
        }
        

        return response()->json([
            'data'=>$data
            ],200);

    }

    public function userDetail(Request $request)
    {
        $applicant_id = $request->applicant_id;

        $candidate = DB::table('users')
            ->leftjoin('users_qualifications', 'users_qualifications.user_id', 'users.id')
            ->leftjoin('user_edu_documents', 'user_edu_documents.user_id', 'users.id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->select('users_qualifications.*', 'user_edu_documents.*', 'user_exps.*')
            ->where('users.id', $applicant_id)
            ->first();

        return response()->json(['status' => 'success', 'data' => $candidate], 200);
    }
    public function getAlluserDetail(Request $request)
    {

         $gender = $request->gender;
        $qualification = $request->qualification;
        $statesearch = $request->statesearch;
       

        $candidate = DB::table('users')
             ->leftjoin('users_qualifications', 'users_qualifications.user_id', 'users.id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', 'users.id')
             ->leftjoin('users_details', 'users_details.user_id', 'users.id')
             ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
              ->leftjoin('user_documents', 'user_documents.user_id', 'users.id')
              ->leftjoin('states as c_states', 'c_states.id', '=', 'user_addresses.c_state_id')
             ->select('users.id','users.fname','users.mname','users.lname','users.mobile','users_details.email1','users_details.gender','user_addresses.c_state_id','c_states.name as c_state_name','users_qualifications.higest_qualification','user_exps.home_salary_as_bank','user_documents.resume')
             ->where('stage','5');
           

              if($gender && $gender !='') {
            $candidate->where('users_details.gender', $gender);
        }

        if($statesearch && $statesearch !='') {
            $candidate->where('user_addresses.c_state_id', $statesearch);
        }

         if($qualification && $qualification !='') {
             $candidate->where('users_qualifications.higest_qualification',$qualification );
         }
           
             $candidate = $candidate->get();
           

        return response()->json(['status' => 'success', 'data' => $candidate], 200);
    }


    public function exportuserdetails()
    {

        $candidate = DB::table('users')
             ->leftjoin('users_qualifications', 'users_qualifications.user_id', 'users.id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', 'users.id')
             ->leftjoin('users_details', 'users_details.user_id', 'users.id')
             ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
              ->leftjoin('user_documents', 'user_documents.user_id', 'users.id')
               ->leftjoin('states as c_states', 'c_states.id', '=', 'user_addresses.c_state_id')
             ->select('users.id','users.fname','users.mname','users.lname','users.mobile','users_details.email1','users_details.gender','c_states.name as c_state_name','users_qualifications.higest_qualification','user_exps.home_salary_as_bank','user_documents.resume')
             ->where('stage','5')
             ->get();

        $headers = [
           'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=candidate-export.csv',
            'Expires' => '0',
            'Pragma' => 'public'
            ];

        //$list = collect($candidate)->map(function($x){ return (array) $x; })->toArray();
        $list = collect($candidate)->map(function($x){
            return [
                'Name' => $x->fname. ' ' .$x->mname. '' .$x->lname,
                'Mobile' => $x->mobile,
                'Email' => $x->email1,
                'Gender' => $x->gender,
                'State' => $x->c_state_name,
                'Highest Qualification' => $x->higest_qualification,
                'Take Home Salary' => $x->home_salary_as_bank,
                'Resume' => $x->resume ? url('documents/resume/'.$x->resume) : 'Not Available',
                
            ];

        })->toArray();

        # add headers for each column in the CSV download
        array_unshift($list, array_keys($list[0]));

        $callback = function() use ($list)
        {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return Response::stream($callback, 200, $headers);
       
    }

    public function mailuser(Request $request){

         $checkedNames = $request->checkedNames;
        $location = $request->location;

         $size_explode=sizeof($checkedNames);

         for($i=0;$i<$size_explode;$i++)
         {
              $user_id = $checkedNames[$i];
             

             $candidate = DB::table('users')
             ->leftjoin('users_details', 'users_details.user_id', 'users.id')
             ->select('users.id','users.fname','users.mname','users.lname','users.mobile','users_details.email1')
             ->where('stage','5')
             ->where('users.id',$user_id)->first();

               


             $toEmail = $candidate->email1;
          $data= ['name'=>$candidate->fname,'email'=>$candidate->email1,'location'=> $location];

           Mail::send('SendMail.userlist', $data, function ($message) use ($toEmail) {
            $message->to($toEmail)->subject('Job');
            $message->from('info@beciljobs.com', 'BecilJobs.com');
        });

             //send sms
            $api_key = env('SMS_API_KEY');
    $from = env('SMS_FROM');
    $campaign = env('SMS_CAMPAIGN');
    $routeid = env('SMS_ROUTE_ID');
    $smstype = env('SMS_TYPE');
            $contacts =$candidate->mobile;
            $name =$candidate->fname;
           
            $msg="Dear ".$name." , ".$location."";
            $sms_text = urlencode($msg);
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, env('SMS_CURL_URL'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=".$campaign."&routeid=".$routeid."&type=".$smstype."&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text);
            $response = curl_exec($ch);
            curl_close($ch);

       }
       if ($size_explode<1) {
           
            return response()->json(['data' => 'Please Select atleast one candidate'], 201);
        }
       
       return response()->json(['data' => 'Message Send successfully'], 200);
           
   
         }
         
}
