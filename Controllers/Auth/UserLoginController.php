<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Auth;
use Illuminate\Support\Facades\Hash;

class UserLoginController extends Controller
{
    

    public function __construct()
    {
        $this->middleware('guest');
    }


	public function showLoginForm()
    {
    	return view('auth.loginuser');
    }

     

    public function loginUser(Request $request)
    {
    	//validate form data 
    	$this->validate($request, [
    		'registration_id' => 'required|numeric',
    		'password' => 'required|min:6'
    	]);
    	//attempt to log the user
    	if(Auth::guard()->attempt(['registration_id' => $request->registration_id, 'password' => $request->password], $request->remember))
        {

            $userid = Auth::user()->stage;

            if($userid =='0'){
                return redirect('/#/otp');
            }
            elseif($userid =='1'){
                return redirect('/#/userdetail');
            } elseif($userid == '2') {
                return redirect('/#/qualification');
            } elseif($userid == '3') {
                return redirect('/#/experience');    
            } elseif($userid == '4') {
                return redirect('/#/document');    
            } elseif($userid == '5') {
                return redirect('/#/profile');    
            } else {
                return redirect('/');
            }

    		//if successfully , then redirect to location 
    		//return redirect('/#/userdetail'); 
             
    	} else {
    	  	
    	//id unsuccessfully, then redirect to login page
    	return redirect()->back()->withInput($request->only('registration_id')); 
        }
    }

   /* public function Userlogin(Request $request)
    {
        //validate form data 
        $this->validate($request, [
            'registration_id' => 'required|numeric',
            'password' => 'required|min:6'
        ]);
        //attempt to log the user
        if(Auth::guard()->attempt(['registration_id' => $request->registration_id, 'password' => $request->password], $request->remember)){

            $userid = Auth::user()->stage;

            if($userid =='0'){
                return redirect('/#/userdetail'); 
            } elseif($userid == '2') {
                return redirect('/#/qualification');
            } elseif($userid == '3') {
                return redirect('/#/experience');    
            } elseif($userid == '4') {
                return redirect('/#/document');    
            } elseif($userid == '5') {
                return redirect('/#/user-dashboard');    
            } else {
                return redirect('/');
            }
            //if successfully , then redirect to location 
            //return redirect('/home'); 
             
        } else {
            
        //id unsuccessfully, then redirect to login page
        return redirect()->back()->withInput($request->only('registration_id')); 
        }
    } */

     public function login(Request $request){
        //validate the form data
        $this->validate($request,[
            
        ]);
        echo "hello";
        $registration_id=$request->registration_id;
        $password=Hash::make($request['password']);
        //$data=$request->registration_id." - ".$request->password;
        if(Auth::guard()->attempt(['registration_id' => $request->registration_id, 'password' => $request->password], $request->remember))
        {

            $userid = Auth::user()->stage;
            $id=Auth::user()->id;
            
          
        return response()->json([
            'data'=>$userid
        ],200);
    
        }

        
           
    }

    public function logout()
    {
        Auth::guard()->logout();
        return redirect('/');
    }
}
