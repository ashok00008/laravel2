<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
Use DB;
use Hash;
Use App\ForgetPassword;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Controllers\Auth\AuthController;

class ForgetPasswordController extends Controller
{
    public function verifymobile(Request $request)
    {   
       
        $req_mobile = $request->mobile;
        
        $get_mobile =DB::table('users')->select('mobile','fname')->where('mobile', $req_mobile)->first();
        $otp=rand(1000,9999);
        $cur_time=date("Y-m-d H:i:s");
        $duration='+10 minutes';
        $otp_exp_time = date('Y-m-d H:i:s', strtotime($duration, strtotime($cur_time)));
        $token =Str::random(20);

        if (!$get_mobile){
            return response()->json(["status"=>"error", "message"=>$req_mobile],201);

        }
        
        $forget = new ForgetPassword();
            $forget->mobile_no = $req_mobile;
            $forget->otp = $otp;
            $forget->otp_exp_time = $otp_exp_time;
            $forget->token = $token;
            $forget->save();
            //send sms
            $api_key = env('SMS_API_KEY');
    $from = env('SMS_FROM');
    $campaign = env('SMS_CAMPAIGN');
    $routeid = env('SMS_ROUTE_ID');
    $smstype = env('SMS_TYPE');
            $contacts =$req_mobile;
            $name =$get_mobile->fname;
            
            $msg="Dear ".$name." , Your One time Password for reset ID is - ".$otp." .OTP is valid for next 10 minutes. Regards Beciljobs.com"; 
            $sms_text = urlencode($msg);
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, env('SMS_CURL_URL'));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=".$campaign."&routeid=".$routeid."&type=".$smstype."&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text);
            $response = curl_exec($ch);
            curl_close($ch);
            //echo $response;
            return response()->json(["status"=>"success", "message"=>$req_mobile],200);
        
    }
    public function verifyOtp(Request $request){

        $req_otp = $request->otp;
        $mobile = $request->mobile;
        $random_pass=rand(10000000,99999999);
        $get_otp = DB::table('forget_passwords')->where('otp', $req_otp)->where('mobile_no', $mobile)->first();
        
        if(is_null($get_otp)){
            return response()->json(["status"=>"error", "message"=>'Your OTP not Matched. <br>आपका ओटीपी मिलान नहीं हुआ |'], 201);
        }  

        $user_otp=$get_otp->otp;
        $user_otp_exp_time = $get_otp->otp_exp_time;
        // $cur_time=date("Y-m-d H:i:s");
        $cur_time=$get_otp->created_at;

        if($get_otp->verify_status === 1) {
            return response()->json(["status"=> 'error', "message"=> "OTP already verified. <br>ओटीपी पहले से सत्यापित है |"]);
        }

        if ($user_otp==$req_otp && $cur_time > $user_otp_exp_time){
            return response()->json(["status"=>"error", "message"=>'Your OTP is Expired.<br> आपका ओटीपी एक्सपायर हो गया है|'], 201);
        } 
        
        // Update OTP status
        $updateOTP= DB::table('forget_passwords')->where(['mobile_no' =>  $get_otp->mobile_no, 'otp'=> $get_otp->otp])->update(['verify_status' =>  1, 'otp'=> '']);
        //send sms
        //get user info
        $get_user_info =DB::table('users')->select('fname','registration_id','password')->where('mobile', $get_otp->mobile_no)->first();
        $fname =$get_user_info->fname;
        $register_id =$get_user_info->registration_id;
        $password = $random_pass;
        $enc_pass=Hash::make($password);
        $update_user_pass= DB::table('users')->where('mobile', $get_otp->mobile_no)->update(['password' =>  $enc_pass]);
        //send sms
        
        $contacts =$get_otp->mobile_no;
        $name =$fname;
        $api_key = env('SMS_API_KEY');
    $from = env('SMS_FROM');
    $campaign = env('SMS_CAMPAIGN');
    $routeid = env('SMS_ROUTE_ID');
    $smstype = env('SMS_TYPE');
        $msg="Dear ".$name." , Your Registration Id is - ".$register_id." and Password is ".$password." Regards Beciljobs.com "; 
        $sms_text = urlencode($msg);
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, env('SMS_CURL_URL'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=".$campaign."&routeid=".$routeid."&type=".$smstype."&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text);
        $response = curl_exec($ch);
        curl_close($ch);

        

        if(!$updateOTP) {
            return response()->json(["status"=> "error", "message"=> "Something went wrong"], 201);
        }
        
        return response()->json(["status"=>"success", "message"=>"OTP verified successfully. <br> ओटीपी सफलतापूर्वक सत्यापित किया गया |"], 200);

    }
}
