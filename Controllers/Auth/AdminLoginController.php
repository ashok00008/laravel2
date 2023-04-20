<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Admin;

class AdminLoginController extends Controller
{

	public function __construct()
	{
		$this->middleware('guest:admin');
	}

    public function showLoginForm()
    {
    	return view('auth.admin-login');
    }

    public function login(Request $request)
    {
    	//validate form data 
    	$this->validate($request, [
    		'email' => 'required|email',
    		'password' => 'required|min:6'
		]);
		$a = Hash::make($request->password);
		echo($a);

		$username = $request->email;
        $data = DB::table('admins')
        ->where('email', $username)
            ->first();
    	//attempt to log the user
    	if(isset($data) && password_verify($request->password, $data->password)){

    		//if successfully , then redirect to location 
			//return redirect()->intended(route('admin.dashboard')); 
			$otp =rand(100000,999999);
			//get mobile and name
			$adm =DB::table('admins')->select('mobile','name')->where('email',$request->email)->first();
		
			$mobile=$adm->mobile;
			$name=$adm->name;
			//update otp 
			$update =DB::table('admins')->where('email',$request->email)->update([
				'otp' =>$otp
			]);
			$enc_email = Crypt::encryptString($request->email);
			//return redirect('/admin#/admin/home');
			       //return 'SMS Send';
			$contacts = $mobile;
			$api_key = env('SMS_API_KEY');
				$from = env('SMS_FROM');
				$campaign = env('SMS_CAMPAIGN');
				$routeid = env('SMS_ROUTE_ID');
				$smstype = env('SMS_TYPE');
			$msg="Dear ". $name." , Your One Time Password (OTP) is ". $otp." OTP valid for next 10 minutes only. "; 
			$sms_text = urlencode($msg);



			//Submit to server
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, env('SMS_CURL_URL'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=".$campaign."&routeid=".$routeid."&type=".$smstype."&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text);
			$response = curl_exec($ch);
			curl_close($ch);
			return redirect()->route('admin.otp.show', $enc_email)->with(['success' => 'OTP is send to your Registered Mobile number '.$this->maskPhoneNumber($contacts).' Successfully.'], 200);
			
    	}
		
    	//id unsuccessfully, then redirect to login page
    	return redirect()->back()->with(['error' => 'Username or Password is Invalid.'], 201); 
	}
	public function ShowOtpForm(){
		return view('auth.otp');
	}
	public function verifyOTP(Request $request){
		$otp =$request->otp;
		$email =$request->email;

    	if(Auth::guard('admin')->attempt(['email' => $request->email, 'otp' => $otp])){


			return redirect('/admin#/admin/home');
		}
		else
		{
			return redirect()->back()->with(['status' => 'error', 'messages' => 'Otp is invalid.'], 201);
		}
	}
	public function ResendOTP($email){
		
		
		$otp =rand(100000,999999);
		//get mobile and name
		$adm =DB::table('admins')->select('mobile','name')->where('email',$email)->first();
		$mobile=$adm->mobile;
			$name=$adm->name;
			//update otp 
			$update =DB::table('admins')->where('email',$email)->update([
				'otp' =>$otp
			]);
		$contacts = $mobile;
			$api_key = env('SMS_API_KEY');
				$from = env('SMS_FROM');
				$campaign = env('SMS_CAMPAIGN');
				$routeid = env('SMS_ROUTE_ID');
				$smstype = env('SMS_TYPE');
			$msg="Dear ". $name." , Your One Time Password (OTP) is ". $otp." OTP valid for next 10 minutes only. "; 
			$sms_text = urlencode($msg);



			//Submit to server
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL, env('SMS_CURL_URL'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=".$campaign."&routeid=".$routeid."&type=".$smstype."&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text);
			$response = curl_exec($ch);
			curl_close($ch);
			if(!$update){
				return redirect()->back()->with(['error' => 'OTP Resend Failed.'], 201);
			}
			return redirect()->back()->with(['success' => 'OTP Resend Successfully.'], 200);
			
	}
	public function ShowResetForm(){
		return view('auth.show-forgetpassword');
	}

	public function SendResetLink(Request $request){
		$email = $request->email;
		$adm = DB::table('admins')->select('email','name','email')->where('email',$email)->first();
		if(is_null($adm)){
			return redirect()->back()->with(['error' => 'Email Not Exist.'], 201);
		}
		$adm_email = $adm->email;

		if($adm_email === $email){
			 //create a new token to be sent to the user.
			 DB::table('password_resets')->insert([
                'email' => $email,
                'token' => str_random(60), //change 60 to any length you want
                'created_at' => Carbon::now()
            ]);

            $tokenData = DB::table('password_resets')->where('email', $email)->where('status', 0)->first();
            $data = [
                'token' => $tokenData->token,
                'emailId' => $email
            ];

            Mail::send('SendMail.reset-password', ['userData' => $data], function ($message) use ($email) {
                $message->to($email)
                    ->subject("Password Reset Link");
                $message->from(env('MAIL_USERNAME'),"Beciljobs.com");
                //$message->from(env('TEST_USEREMAIL'), "Beciljobs.com");
            });
			return redirect()->back()->with(['success' => 'A reset Password Link is sent to your Email.', 'messages' => $email], 200);	
		}
		
		
	}
	public function forgetPasswordForm($token)
    {
        return view('resetpassword')->with(['token' => $token]);
	}
	
	public function forgetPasswordStore(Request $request)
    {
        $password = $request->password;
        $token = $request->urlToken;
        $password = Hash::make($password);

        // CHECK TOKEN IS EXIST
        $checkToken = DB::table('password_resets')->where('token', $token)->where('status', 0)->first();

        if (!$checkToken) {
            return back()->with(['status' => 'error', 'messages_error' => 'Token mismatch or password already reset.']);
        }

        // TOKEN EXPIRY CHECK
        $tokenExpiry = DB::table('password_resets')->where('token', $token)->where('status', 0)->where('created_at', '>', Carbon::now()->subHours(1))->first();

        if (!$tokenExpiry) {
            return back()->with(['status' => 'error', 'messages' => 'Token Expire']);
        }

        $updateResetPassword = '';

        if ($tokenExpiry) {
            $updateResetPassword = DB::table('admins')->where('email', $checkToken->email)->update(['password' => $password]);
        } 

        if (!$updateResetPassword) {
            return redirect()->back()->with(['status' => 'error', 'messages' => 'Something went wrong.'], 201);
        }

        DB::table('password_resets')->where('token', $token)->update(['status' => 1]);
        return redirect()->back()->with(['status' => 'success', 'messages' => 'Your password has been changed Successfully.'], 200);
    }

    public function logout()
    {
		Auth::guard('admin')->logout();
		//return redirect()->route('/admin');
        return redirect('/admin');
    }
    function maskPhoneNumber($number){
    
		$mask_number =  str_repeat("*", strlen($number)-4) . substr($number, -4);
		
		return $mask_number;
	}
}
