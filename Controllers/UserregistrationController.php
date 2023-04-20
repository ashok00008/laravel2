<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Language;
use App\Religion;
use App\Category;
use App\BloodGroup;
use App\State;
use App\Citie;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Routing\Redirector;
use DB;
use Auth;
 

class UserregistrationController extends Controller
{   

     
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {    
       
    }

    public function checklogin()
    {
        if(isset(Auth::user()->id))
        {
        $data = Auth::user()->id;
        }
        $data = "Notlogin";

        return response()->json([
            'data'=>$data
            ],200);

    }

    public function getreligion()
    {

        $data = Religion::select('id','religion_name','status')->get();
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function getcategory()
    {

        $data = Category::select('id','cat_name','status')->get();
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function getbloodgroup()
    {

        $data = BloodGroup::select('id','blood_group','status')->get();
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function getlanguage()
    {

        $data = Language::select('id','language','status')->get();
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function getstate()
    {

        $data = State::select('id','name','status')->where('country_id','101')->get();
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function getcity($id)
    {

        $data = Citie::select('id','name','status')->where('state_id',$id)->get();
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function checkmobile($id)
    {

        $data = User::select('mobile')->where('mobile',$id)->count();
        //$res=sizeof($data)

        return response()->json([
            'data'=>$data
        ],200);
        
    }

    public function checkaadhar($id)
    {
        $string=$id;
        $last_aadhar=substr($id,8);
        $result = (int)$string;
        $key = '04061996';
        $key2=(int)$last_aadhar;
        $result=$result*2;
        $result=$result+$key;
        $result=strrev($result);
        $pk=($result*$key2)+$key2;
        $data = User::select('public_key')->where('public_key',$pk)->count();
        //$res=sizeof($data)

        return response()->json([
            'data'=>$data
        ],200);
                //return response($res);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    public function verifymobile(Request $request)
    {   
        //$getid = $request->id;
        $req_otp = $request->otp;
        $userid = Auth::user()->id;
        $userotp = Auth::user()->otp_mobile;
        $usermobile = Auth::user()->mobile;
        $stage = Auth::user()->stage;
        $user_otp_exp_time = Auth::user()->otp_exp_time;
        $cur_time=date("Y-m-d H:i:s");
        //$uid = decrypt($id);
       // $otps = decrypt($oid);
        //$where = ['id'=>$uid , 'otp_mobile'=>$otps];
        //$data = User::where($where)->get();
        //$data = User::find($id);
        //$arr=json_decode($data);
        //if(count($data)>0)
        if($userotp==$req_otp && $cur_time<=$user_otp_exp_time){
            $users = User::find($userid);
       $users->stage = '1'; 

       $users->save();
           return response()->json(["type"=>"success", "message"=>"Your mobile number is verified!","response"=>"1","stage"=>$users->stage]);
            
        }
        else if($userotp==$req_otp && $cur_time>$user_otp_exp_time){
           return response()->json(["type"=>"error", "message"=>"Your OTP Expired!","response"=>"1","stage"=>$stage]);
            
        }
        else if($userotp!=$req_otp && $cur_time<=$user_otp_exp_time){
           return response()->json(["type"=>"$userotp", "message"=>"OTP Not Matched !","response"=>"1","stage"=>$stage]);
            
        }
        else{
            return response()->json(["type"=>"error", "message"=>"Mobile number verification failed","response"=>"0","stage"=>$stage]);
        }
    }

  

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $otp=rand(1000,9999);
        $last_mobile=substr($request['mobile'],6);
        $last_aadhar=substr($request['aadhar_no'],8);
        $reg_id=$last_aadhar."".$last_mobile;

        $string=$request['aadhar_no'];
        $result = (int)$string;
        $key = '04061996';
        $key2=(int)$last_aadhar;
        $result=$result*2;
        $result=$result+$key;
        $result=strrev($result);
        $result=($result*$key2)+$key2;

        $cur_time=date("Y-m-d H:i:s");
        $duration='+5 minutes';
        $otp_exp_time = date('Y-m-d H:i:s', strtotime($duration, strtotime($cur_time)));
    

        $this->validate($request,[
            'fname'=>'required|min:3|max:30',
            'aadhar_no'=>'required|min:12',
            'mobile'=>'required|min:10',
            'password'=>'required|min:6',

        ]);

       
       $user = New User();
       $user->registration_id = $reg_id;
       $user->fname = $request->fname;
       $user->mname = $request->mname;
       $user->lname = $request->lname;
       $user->mobile = $request->mobile;
       $user->otp_mobile = $otp;
       $user->public_key = $result;
       $user->aadhar_no = encrypt($request['aadhar_no']);
       $user->password = Hash::make($request['password']);
       $user->otp_exp_time = $otp_exp_time;
       $user->save();
       $idget = $user->id;
       $mget = $user->otp_mobile; 
       $otp = encrypt($idget);
       $get = encrypt($mget);


        $contacts = $request->mobile;       
        $api_key = env('SMS_API_KEY');
    $from = env('SMS_FROM');
    $campaign = env('SMS_CAMPAIGN');
    $routeid = env('SMS_ROUTE_ID');
    $smstype = env('SMS_TYPE');
$msg="Dear ". $request->fname." , Your Registration is successfully on BecilJobs and your Registration Id - ".$reg_id." Use Registration Id for Login with your password"; 
$sms_text = urlencode($msg);

$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, env('SMS_CURL_URL'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=".$campaign."&routeid=".$routeid."&type=".$smstype."&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text);
$response = curl_exec($ch);
curl_close($ch);
echo $response;

return response()->json([
            'otp'=> $otp, 'get'=> $get
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


    public function resendotp()
    {
        $userid = Auth::user()->id;
        $fname = Auth::user()->fname;
        $otp=rand(1000,9999);
        $cur_time=date("Y-m-d H:i:s");
        $duration='+5 minutes';
        $otp_exp_time = date('Y-m-d H:i:s', strtotime($duration, strtotime($cur_time)));
        $users = User::find($userid);
        $users->otp_mobile = $otp;
        $users->otp_exp_time = $otp_exp_time;
      // $users->stage = '1'; 
       $users->save();        
        $api_key = env('SMS_API_KEY');
    $contacts = Auth::user()->mobile;
    $from = env('SMS_FROM');
    $campaign = env('SMS_CAMPAIGN');
    $routeid = env('SMS_ROUTE_ID');
    $smstype = env('SMS_TYPE');

$msg2="Dear ". $fname." , Your 4 Digit One Time Password (OTP) for mobile verification is ".$otp." and your OTP is valid for next 10 minutes";
$sms_text2 = urlencode($msg2);

$ch2 = curl_init();
curl_setopt($ch2,CURLOPT_URL, env('SMS_CURL_URL'));
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch2, CURLOPT_POST, 1);
curl_setopt($ch2, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=".$campaign."&routeid=".$routeid."&type=".$smstype."&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text2);
$response2 = curl_exec($ch2);
curl_close($ch2);
echo $response2;

return response()->json([
            
        ],200);

       

       
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

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    public function getUserData(Request $request){
        $reg_id = $request->reg_id;
        $data = DB::table('users')->where('registration_id',$reg_id)->first();
        
        return response()->json(['data'=>$data],200);
    }

    public function getUserDocs(Request $request){
        $reg_id = $request->reg_id;
        $user_doc = DB::table('user_documents')->where('user_documents.user_id',$reg_id)->first();
        $user_edu_doc = DB::table('user_edu_documents')->where('user_edu_documents.user_id',$reg_id)->first();
        return response()->json(['user_doc'=>$user_doc, 'user_edu_doc'=>$user_edu_doc],200);
    }
}
