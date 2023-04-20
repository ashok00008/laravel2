<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Admin;
use Illuminate\Support\Facades\Hash;
use Auth;

class AdminController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('admin.admindash');
       
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
        
		Auth::guard('admin')->logout();
		//return redirect()->route('/admin');
        return redirect('/admin');
	}
}
