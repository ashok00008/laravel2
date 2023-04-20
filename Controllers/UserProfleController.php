<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UsersDetails;
use App\UserAddress;
use App\User;
use App\Citie;
use DB;
use Auth;


class UserProfleController extends Controller
{
   

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function store(Request $request)
    {

 
        $this->validate($request,[
            'gender'=>'required',
            'father_name'=>'required',
            'father_contact'=>'required',
            'pan_no'=>'required',
            'dob'=>'required',
            'category_id' => 'required',
            'nationality' => 'required',
            'religion' => 'required',
            'email1' => 'required',
            'bloodgroup' => 'required',

        ]);
       $userdetail = New UsersDetails();
       $userdetail->user_id = Auth::user()->id;
       $userdetail->gender = $request->gender;
       $userdetail->father_name = $request->father_name;
       $userdetail->father_contact = $request->father_contact;
       $userdetail->pan_no = $request->pan_no;
       $userdetail->dob = $request->dob;
       $userdetail->passport_no = $request->passport;
       $userdetail->category_id = $request->category_id;
       $userdetail->nationality = $request->nationality;
       $userdetail->religion_id = $request->religion;
       $userdetail->mobile1 = $request->mobile1;
       $userdetail->email1 = $request->email1;
       $userdetail->email2 = $request->email2;
       $userdetail->language_id = $request->language;
       $userdetail->blood_group_id = $request->bloodgroup;
       $userdetail->prefered_location1 = $request->preferred_location_1;
       $userdetail->prefered_location2 = $request->preferred_location_2;
       $userdetail->save();

       $useraddress = New UserAddress();
       $useraddress->user_id = Auth::user()->id;
       $useraddress->c_first_add = $request->c_first_add;
       $useraddress->c_second_add = $request->c_second_add;
       $useraddress->c_landmark = $request->c_landmark;
       $useraddress->c_state_id = $request->c_state_id;
       $useraddress->c_city_id = $request->c_city_id;
       $useraddress->c_pincode = $request->c_pincode;
       $useraddress->p_first_add = $request->p_first_add;
       $useraddress->p_second_add = $request->p_second_add;
       $useraddress->p_landmark = $request->p_landmark;
       $useraddress->p_state_id = $request->p_state_id;
       $useraddress->p_city_id = $request->p_city_id;
       $useraddress->p_pincode = $request->p_pincode;
       $useraddress->save();

       $uid = $useraddress->user_id;
       if($uid){
       $users = User::find($uid);
       $users->stage = '2'; 
       $users->save();
       
        }
        return response()->json([
            'message'=>'success'
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
    public function getcityuser()
    {
      $user_id=Auth::user()->id;
      $data1 = DB::table('users') 
            ->leftjoin('user_addresses', 'user_addresses.user_id', '=', 'users.id')
            ->select('user_addresses.p_state_id','user_addresses.c_state_id')
            ->where('users.id',$user_id)
            ->first();

            $id=$data1->p_state_id;

            $data = Citie::select('id','name','status')->where('state_id',$id)->get();
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function getcurcityuser()
    {
      $user_id=Auth::user()->id;
      $data1 = DB::table('users') 
            ->leftjoin('user_addresses', 'user_addresses.user_id', '=', 'users.id')
            ->select('user_addresses.p_state_id','user_addresses.c_state_id')
            ->where('users.id',$user_id)
            ->first();

            $id=$data1->c_state_id;

            $data = Citie::select('id','name','status')->where('state_id',$id)->get();
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function edit()
    {
      $user_id=Auth::user()->id;
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
            ->select('users.id','users.fname as first_name','users.mname as mid_name', 'users.lname as last_name', 'users.mobile','users.aadhar_no' ,'users.registration_id', 'users_details.email1', 'users_details.email2', 'users_details.mobile1','users_details.father_name','users_details.nationality','users_details.father_contact','users_details.gender','users_details.pan_no','users_details.dob','users_details.language_id as language','users_details.blood_group_id as bloodgroup','users_details.passport_no as passport','categories.cat_name','users_details.category_id','users_details.religion_id as religion','religions.religion_name','blood_groups.blood_group as blood_group_name','languages.language as language_name','users_details.prefered_location1 as preferred_location_1','users_details.prefered_location2 as preferred_location_2','user_addresses.p_first_add','user_addresses.p_second_add','user_addresses.p_landmark','user_addresses.p_pincode','user_addresses.p_state_id','user_addresses.p_city_id','cities.name as p_city','states.name as p_state','user_addresses.c_first_add','user_addresses.c_second_add','user_addresses.c_landmark','user_addresses.c_pincode','c_cities.name as c_city_name','c_states.name as c_state_name','user_addresses.c_state_id','user_addresses.c_city_id','user_exps.latest_emp_cname','user_exps.latest_emp_from','user_exps.latest_emp_to','user_exps.prev_emp_cname','user_exps.prev_emp_from','user_exps.prev_emp_to','user_exps.total_exp_year','user_exps.total_exp_month','user_exps.relevant_exp_year','user_exps.relevant_exp_month','user_exps.current_salary_monthly','user_exps.home_salary_as_bank','users_qualifications.eight_school_name','users_qualifications.eight_passing_year','users_qualifications.eight_marks','users_qualifications.ten_board_name','users_qualifications.ten_passing_year','users_qualifications.ten_marks','users_qualifications.ten_stream','users_qualifications.twelve_board_name','users_qualifications.twelve_passing_year','users_qualifications.twelve_marks','users_qualifications.twelve_stream','users_qualifications.diploma_institute_name','users_qualifications.diploma_name','users_qualifications.diploma_passing_year','users_qualifications.diploma_marks','users_qualifications.diploma_stream','users_qualifications.ug_degree','users_qualifications.ug_branch','users_qualifications.ug_university','users_qualifications.ug_year','users_qualifications.ug_marks','users_qualifications.pg_degree','users_qualifications.pg_branch','users_qualifications.pg_university','users_qualifications.pg_year','users_qualifications.pg_marks','users_qualifications.pg_degree','users_qualifications.additional_institute_name','users_qualifications.additional_qual','users_qualifications.additional_qual_marks','users_qualifications.additional_qual_year','user_documents.photograph','user_documents.signature','user_documents.resume','user_documents.caste_certificate','user_edu_documents.ten_doc','user_edu_documents.twelve_doc','user_edu_documents.diploma_doc','user_edu_documents.ug_doc','user_edu_documents.pg_doc','user_edu_documents.add_cert_doc')
            ->where('users.id',$user_id)
            ->first();
        return response()->json([
            'data'=>$data
        ],200);
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
        $this->validate($request,[
            'gender'=>'required',
            'father_name'=>'required',
            'father_contact'=>'required',
            'pan_no'=>'required',
            'dob'=>'required',
            'category_id' => 'required',
            'nationality' => 'required',
            'religion' => 'required',
            'email1' => 'required',
            
            'bloodgroup' => 'required',

        ]);
       $userdetail = New UsersDetails();
       $user_id = Auth::user()->id;
       $userdetail = UsersDetails::where('user_id', '=',  $user_id)->first();
       $userdetail->gender = $request->gender;
       $userdetail->father_name = $request->father_name;
       $userdetail->father_contact = $request->father_contact;
       $userdetail->pan_no = $request->pan_no;
       $userdetail->dob = $request->dob;
       $userdetail->passport_no = $request->passport;
       $userdetail->category_id = $request->category_id;
       $userdetail->nationality = $request->nationality;
       $userdetail->religion_id = $request->religion;
       $userdetail->mobile1 = $request->mobile1;
       $userdetail->email1 = $request->email1;
       $userdetail->email2 = $request->email2;
       $userdetail->language_id = $request->language;
       $userdetail->blood_group_id = $request->bloodgroup;
       $userdetail->prefered_location1 = $request->preferred_location_1;
       $userdetail->prefered_location2 = $request->preferred_location_2;
       $userdetail->save();

       $useraddress = New UserAddress();
       $user_id = Auth::user()->id;
       $useraddress = UserAddress::where('user_id', '=',  $user_id)->first();
       $useraddress->c_first_add = $request->c_first_add;
       $useraddress->c_second_add = $request->c_second_add;
       $useraddress->c_landmark = $request->c_landmark;
       $useraddress->c_state_id = $request->c_state_id;
       $useraddress->c_city_id = $request->c_city_id;
       $useraddress->c_pincode = $request->c_pincode;
       $useraddress->p_first_add = $request->p_first_add;
       $useraddress->p_second_add = $request->p_second_add;
       $useraddress->p_landmark = $request->p_landmark;
       $useraddress->p_state_id = $request->p_state_id;
       $useraddress->p_city_id = $request->p_city_id;
       $useraddress->p_pincode = $request->p_pincode;
       $useraddress->save();

       
       
        return response()->json([
            'message'=>'success'
        ],200);
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

}
