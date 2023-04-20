<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UsersQualification;
use App\User;
use DB;
use Auth;


class UserEducationController extends Controller
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
    public function store(Request $request)
    {
       $this->validate($request,['higest_qualification' => 'required',
            

        ]);
       $userqualification = New UsersQualification();
       $userqualification->user_id = Auth::user()->id;
       $userqualification->eight_school_name = $request->eight_school_name;
       $userqualification->eight_passing_year = $request->eight_passing_year;
       $userqualification->eight_marks = $request->eight_marks;
       $userqualification->ten_board_name = $request->ten_board_name;
       $userqualification->ten_passing_year = $request->ten_passing_year;
       $userqualification->ten_marks     = $request->ten_marks;
       $userqualification->ten_stream = $request->ten_stream;
       $userqualification->twelve_board_name = $request->twelve_board_name;
       $userqualification->twelve_passing_year = $request->twelve_passing_year;
       $userqualification->twelve_marks = $request->twelve_marks;
       $userqualification->twelve_stream = $request->twelve_stream;
       $userqualification->diploma_institute_name = $request->diploma_institute_name;
       $userqualification->diploma_name = $request->diploma_name;
       $userqualification->diploma_passing_year = $request->diploma_passing_year;
       $userqualification->diploma_marks = $request->diploma_marks;
       $userqualification->diploma_stream = $request->diploma_stream;
       $userqualification->ug_degree = $request->ug_degree;
       $userqualification->ug_branch = $request->ug_branch;
       $userqualification->ug_university = $request->ug_university;
       $userqualification->ug_year = $request->ug_year;
       $userqualification->ug_marks = $request->ug_marks;
       $userqualification->ug_edu_type = $request->ug_edu_type;
       $userqualification->pg_degree = $request->pg_degree;
       $userqualification->pg_branch = $request->pg_branch;
       $userqualification->pg_university = $request->pg_university;
       $userqualification->pg_year = $request->pg_year;
       $userqualification->pg_marks = $request->pg_marks;
       $userqualification->pg_edu_type = $request->pg_edu_type;
       $userqualification->additional_institute_name = $request->additional_institute_name;
       $userqualification->additional_qual = $request->additional_qual;
       $userqualification->additional_qual_year = $request->additional_qual_year;
       $userqualification->additional_qual_marks = $request->additional_qual_marks;
       $userqualification->additional_qual_type = $request->additional_qual_type;
       $userqualification->higest_qualification = $request->higest_qualification;
       $userqualification->save();

       $uid = $userqualification->user_id;
       if($uid){
       $users = User::find($uid);
       $users->stage = '3'; 
       $users->save();
        return response()->json([
            'message'=>'success'
        ],200); 
      }
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
            ->select('users.id','users.fname as first_name','users.mname as mid_name', 'users.lname as last_name', 'users.mobile','users.aadhar_no' ,'users.registration_id', 'users_details.email1', 'users_details.email2', 'users_details.mobile1','users_details.father_name','users_details.father_contact','users_details.gender','users_details.pan_no','users_details.dob','users_details.passport_no','categories.cat_name','religions.religion_name','blood_groups.blood_group','languages.language','users_details.prefered_location1','users_details.prefered_location2','user_addresses.p_first_add','user_addresses.p_second_add','user_addresses.p_landmark','user_addresses.p_pincode','cities.name as p_city','states.name as p_state','user_addresses.c_first_add','user_addresses.c_second_add','user_addresses.c_landmark','user_addresses.c_pincode','c_cities.name as c_city_name','c_states.name as c_state_name','user_exps.latest_emp_cname','user_exps.latest_emp_from','user_exps.latest_emp_to','user_exps.prev_emp_cname','user_exps.prev_emp_from','user_exps.prev_emp_to','user_exps.total_exp_year','user_exps.total_exp_month','user_exps.relevant_exp_year','user_exps.relevant_exp_month','user_exps.current_salary_monthly','user_exps.home_salary_as_bank','users_qualifications.eight_school_name','users_qualifications.eight_passing_year','users_qualifications.eight_marks','users_qualifications.ten_board_name','users_qualifications.ten_passing_year','users_qualifications.ten_marks','users_qualifications.ten_stream','users_qualifications.twelve_board_name','users_qualifications.twelve_passing_year','users_qualifications.twelve_marks','users_qualifications.twelve_stream','users_qualifications.diploma_institute_name','users_qualifications.diploma_name','users_qualifications.diploma_passing_year','users_qualifications.diploma_marks','users_qualifications.diploma_stream','users_qualifications.ug_degree','users_qualifications.ug_branch','users_qualifications.ug_university','users_qualifications.ug_year','users_qualifications.ug_edu_type','users_qualifications.ug_marks','users_qualifications.pg_degree','users_qualifications.pg_branch','users_qualifications.pg_university','users_qualifications.pg_year','users_qualifications.pg_edu_type','users_qualifications.pg_marks','users_qualifications.pg_degree','users_qualifications.additional_institute_name','users_qualifications.additional_qual','users_qualifications.additional_qual_marks','users_qualifications.additional_qual_year','user_documents.photograph','user_documents.signature','user_documents.resume','user_documents.caste_certificate','user_edu_documents.ten_doc','user_edu_documents.twelve_doc','user_edu_documents.diploma_doc','user_edu_documents.ug_doc','user_edu_documents.pg_doc','user_edu_documents.add_cert_doc')
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
        $userqualification = New UsersQualification();
       $user_id = Auth::user()->id;
       $userqualification = UsersQualification::where('user_id', '=',  $user_id)->first();
       $userqualification->eight_school_name = $request->eight_school_name;
       $userqualification->eight_passing_year = $request->eight_passing_year;
       $userqualification->eight_marks = $request->eight_marks;
       $userqualification->ten_board_name = $request->ten_board_name;
       $userqualification->ten_passing_year = $request->ten_passing_year;
       $userqualification->ten_marks     = $request->ten_marks;
       $userqualification->ten_stream = $request->ten_stream;
       $userqualification->twelve_board_name = $request->twelve_board_name;
       $userqualification->twelve_passing_year = $request->twelve_passing_year;
       $userqualification->twelve_marks = $request->twelve_marks;
       $userqualification->twelve_stream = $request->twelve_stream;
       $userqualification->diploma_institute_name = $request->diploma_institute_name;
       $userqualification->diploma_name = $request->diploma_name;
       $userqualification->diploma_passing_year = $request->diploma_passing_year;
       $userqualification->diploma_marks = $request->diploma_marks;
       $userqualification->diploma_stream = $request->diploma_stream;
       $userqualification->ug_degree = $request->ug_degree;
       $userqualification->ug_branch = $request->ug_branch;
       $userqualification->ug_university = $request->ug_university;
       $userqualification->ug_year = $request->ug_year;
       $userqualification->ug_marks = $request->ug_marks;
       $userqualification->ug_edu_type = $request->ug_edu_type;
       $userqualification->pg_degree = $request->pg_degree;
       $userqualification->pg_branch = $request->pg_branch;
       $userqualification->pg_university = $request->pg_university;
       $userqualification->pg_year = $request->pg_year;
       $userqualification->pg_marks = $request->pg_marks;
       $userqualification->pg_edu_type = $request->pg_edu_type;
       $userqualification->additional_institute_name = $request->additional_institute_name;
       $userqualification->additional_qual = $request->additional_qual;
       $userqualification->additional_qual_year = $request->additional_qual_year;
       $userqualification->additional_qual_marks = $request->additional_qual_marks;
       $userqualification->additional_qual_type = $request->additional_qual_type;
       $userqualification->higest_qualification = $request->higest_qualification;
       $userqualification->save();

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
