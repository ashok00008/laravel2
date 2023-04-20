<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApplyJob;
use App\TempApplyJob;
use App\User;
use App\UsersDetails;
use Auth;
use DB;
use Session;
use App\OnlinePayment;
use Mail;
use App\Mail\JobApplication;

class ApplyJobController extends Controller
{
     public function __construct()
    {
        $this->middleware('auth');
        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
    public function store(Request $request,$id)
    {
         $this->validate($request,[
            

        ]);
         $userid=Auth::user()->id;
         if($userid=="")
         {
            return redirect('/login');
         }
         $jid="JA-".Auth::user()->id."/".Auth::user()->registration_id."/".$id;
         $jobid=$jid;
       $applyjob = New TempApplyJob();
       $applyjob->user_id = Auth::user()->id;
       $applyjob->job_id = $id;
       $applyjob->application_id = $jid;
       $applyjob->user_reg_id = Auth::user()->registration_id;
       $applyjob->save();



       $data = DB::table('users')
       ->leftjoin('users_details', 'users_details.user_id', '=', 'users.id')
       ->select('users.id','users.fname','users.mname', 'users.lname', 'users.mobile','users.aadhar_no' ,'users.registration_id', 'users_details.email1')
       ->where('users.id',$userid)
       ->first();
       $myArray = json_decode(json_encode($data), true);
        $to = $myArray['email1'];
       $name=$myArray['fname'];
        $mobile=$myArray['mobile'];
        $subject="Job Application";
        
        
        if($to!="")
        {
        Mail::to($to)->send(new JobApplication($name,$mobile,$jobid));

        }
        //return 'Email Send';

        
$contacts = $myArray['mobile'];
$api_key = env('SMS_API_KEY');
    $from = env('SMS_FROM');
    $campaign = env('SMS_CAMPAIGN');
    $routeid = env('SMS_ROUTE_ID');
    $smstype = env('SMS_TYPE');
$msg="Dear ". $name." , Your job application is applied successfully application no. is ". $jobid." please make payment "; 
$sms_text = urlencode($msg);



//Submit to server
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, env('SMS_CURL_URL'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=".$campaign."&routeid=".$routeid."&type=".$smstype."&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text);
$response = curl_exec($ch);
curl_close($ch);
echo $response;

        return response()->json([
            'message'=>'success','userid'=>$userid
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    public function checkUserTempApplyJob()
    {
        $userId = Auth::user()->id;
        $tempappliedjobs = TempApplyJob::select('job_id')
        ->where('user_id', $userId)
        ->get();

        return response()->json($tempappliedjobs, 200);
    }
    public function checkUserApplyJob(){
        $userId = Auth::user()->id;
        $appliedjobs = ApplyJob::select('job_id')
        ->where('user_id', $userId)
        ->get();

        return response()->json($appliedjobs, 200);
    }
    public function checkUserApplyPayment(){
        $userId = Auth::user()->id;
        $applypayment = OnlinePayment::select('job_id')
        ->where('user_id', $userId)
        ->get();

        return response()->json($applypayment, 200);
    }

    public function changeApplyJobStatus(Request $request)
    {
        $is_screening = $request->is_screening;
        $application_id = $request->application_id;

        $applyjob = ApplyJob::where('application_id', $application_id)->first();

        if ($applyjob != '') {
            $applyjob->update(['is_screening' => $is_screening]);

            return response()->json(['status' => 'success', 'message' => 'Application ' . $application_id . ' is ' . $is_screening], 200);
        }

        return response()->json(['status' => 'error', 'message' => 'Something went wrong'], 200);
    }
}
