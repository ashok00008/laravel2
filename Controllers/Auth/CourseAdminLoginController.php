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
use Session;

class CourseAdminLoginController extends Controller
{

	public function __construct()
	{
		$this->middleware('guest:course-admin')->except('logout');
	}

    public function showLoginForm()
    {
    	return view('auth.course-admin-login');
    }

    public function login(Request $request)
    {
    	//validate form data 
    	$this->validate($request, [
    		'email' => 'required|email',
    		'password' => 'required|min:6'
		]);
		$a = Hash::make($request->password);
		// echo($a);

		$username = $request->email;
        $data = DB::table('course-admins')
        ->where('email', $username)
            ->first();
    	//attempt to log the user
    	if(isset($data) && password_verify($request->password, $data->password)){

            if(Auth::guard('course-admin')->attempt(['email' => $request->email, 'password' => $request->password])){
                // dd(Auth::user('course-admin'));
                // session()->put('course-admin', ['id' => $data->id, 'name' => 'New Name', 'user_role' => $data->user_role, 'email' => $data->email, 'location' => $data->location, 'remember_token' => $data->remember_token, 'mobile' => $data->mobile, 'status' => $data->status, 'created_at' => $data->created_at, 'updated_at' => $data->updated_at]);
                return redirect('/course-admin');
            }
            // return view('courses.courseadmindash');
    	}
		
    	//id unsuccessfully, then redirect to login page
    	return redirect()->back()->with(['error' => 'Username or Password is Invalid.'], 201); 
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
		Auth::guard('course-admin')->logout();
		//return redirect()->route('/admin');
        return redirect('/course-admin');
    }
    function maskPhoneNumber($number){
    
		$mask_number =  str_repeat("*", strlen($number)-4) . substr($number, -4);
		
		return $mask_number;
	}
}
