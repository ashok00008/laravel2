<?php

namespace App\Http\Controllers;

use App\Examdetail;
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

use Illuminate\Http\Request;

class ExamdetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   public function index()
    {
        

       $userid = Auth::user()->id;

            
              $userid = Auth::user()->id;

            
              $data = DB::table('examdetails') 
            ->leftjoin('users_details', 'users_details.user_id', '=', 'examdetails.user_id')
            ->leftjoin('apply_jobs', 'apply_jobs.application_id', '=', 'examdetails.application_id')
            ->leftjoin('jobs', 'jobs.id', '=', 'examdetails.job_id')
            ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
             ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', '=', 'examdetails.user_id')
            ->leftjoin('cities', 'cities.id', '=', 'user_addresses.p_city_id')
            ->leftjoin('cities as c_cities', 'c_cities.id', '=', 'user_addresses.c_city_id')
            ->leftjoin('states', 'states.id', '=', 'user_addresses.p_state_id')
            ->leftjoin('states as c_states', 'c_states.id', '=', 'user_addresses.c_state_id')
         ->select('examdetails.id','advertisements.advertisement_no','designations.designation','examdetails.application_id','jobs.exam_conducted','apply_jobs.is_screening')
             ->where('examdetails.user_id',$userid)
             
            ->get();
            
             return response()->json([
            'data'=>$data
            ],200);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        
            
            $data1= DB::table('examdetails') 
            ->leftjoin('users_details', 'users_details.user_id', '=', 'examdetails.user_id')
             ->leftjoin('users', 'users.id', '=', 'examdetails.user_id')
            ->leftjoin('jobs', 'jobs.id', '=', 'examdetails.job_id')
            ->leftjoin('user_documents', 'user_documents.user_id', '=', 'examdetails.user_id')
            ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
             ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', '=', 'examdetails.user_id')
            ->leftjoin('cities', 'cities.id', '=', 'user_addresses.p_city_id')
            ->leftjoin('cities as c_cities', 'c_cities.id', '=', 'user_addresses.c_city_id')
            ->leftjoin('states', 'states.id', '=', 'user_addresses.p_state_id')
            ->leftjoin('states as c_states', 'c_states.id', '=', 'user_addresses.c_state_id')
         ->select('examdetails.id','examdetails.application_id','examdetails.user_reg_id','examdetails.exam_datetime',
         'examdetails.venue','users.fname','users.mname', 'users.lname', 'users.mobile','users_details.father_name',
         'users_details.dob','users_details.gender','user_addresses.p_first_add','user_addresses.p_second_add',
         'user_addresses.p_landmark','user_addresses.p_pincode','cities.name as p_city','states.name as p_state',
         'user_addresses.c_first_add','user_addresses.c_second_add','user_addresses.c_landmark','user_addresses.c_pincode',
         'c_cities.name as c_city_name','c_states.name as c_state_name','user_documents.photograph','user_documents.signature',
         'advertisements.advertisement_no','designations.designation','examdetails.application_id','users.aadhar_no','users_details.email1','jobs.special_instruction_admitcard')
             ->where('examdetails.id',$id)
            ->first();
            $myArray = json_decode(json_encode($data1), true);
             $dec_aadhar = decrypt($myArray['aadhar_no']);
             $data1->dec_aadhar = $dec_aadhar;
             
             $data2 =DB::table('instruction_admit_cards')
             ->select('general_instruction')
             ->get();

             $merged = $data2->merge($data1);
             return response()->json(['data'=>$merged
            ],200);
            
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Examdetail  $examdetail
     * @return \Illuminate\Http\Response
     */
    public function show(Examdetail $examdetail)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Examdetail  $examdetail
     * @return \Illuminate\Http\Response
     */
    public function edit(Examdetail $examdetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Examdetail  $examdetail
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Examdetail $examdetail)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Examdetail  $examdetail
     * @return \Illuminate\Http\Response
     */
    public function destroy(Examdetail $examdetail)
    {
        //
    }
}
