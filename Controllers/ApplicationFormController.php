<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\ApplyJob;
use DB;
use Auth;

class ApplicationFormController extends Controller
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
    public function index($id)
    {
        //$userid = Auth::user()->id;
        $data = DB::table('apply_jobs') 
            ->leftjoin('jobs', 'jobs.id', '=', 'apply_jobs.job_id' )
            ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id' )
            ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id' )
            ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id' )
            ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id' )
            ->leftjoin('joborganisations', 'joborganisations.id', '=', 'jobs.org_id' )
            ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id' )
            ->leftjoin('users', 'users.id', '=', 'apply_jobs.user_id')
            ->leftjoin('users_details', 'users_details.user_id', '=', 'apply_jobs.user_id')
            ->leftjoin('user_addresses', 'user_addresses.user_id', '=', 'apply_jobs.user_id')
            ->leftjoin('user_exps', 'user_exps.user_id', '=', 'apply_jobs.user_id')
            ->leftjoin('users_qualifications', 'users_qualifications.user_id', '=', 'apply_jobs.user_id')
            ->leftjoin('user_documents', 'user_documents.user_id', '=', 'apply_jobs.user_id')
            ->leftjoin('user_edu_documents', 'user_edu_documents.user_id', '=', 'apply_jobs.user_id')
            ->leftjoin('blood_groups', 'blood_groups.id', '=', 'users_details.blood_group_id')
            ->leftjoin('categories', 'categories.id', '=', 'users_details.category_id')
            ->leftjoin('religions', 'religions.id', '=', 'users_details.religion_id')
            ->leftjoin('languages', 'languages.id', '=', 'users_details.language_id')
            ->leftjoin('cities', 'cities.id', '=', 'user_addresses.p_city_id' )
            ->leftjoin('cities as c_cities', 'c_cities.id', '=', 'user_addresses.c_city_id')
            ->leftjoin('states', 'states.id', '=', 'user_addresses.p_state_id')
            ->leftjoin('states as c_states', 'c_states.id', '=', 'user_addresses.c_state_id')
            ->leftjoin('payments', 'payments.apply_id', '=', 'apply_jobs.job_id')
            ->select( 'apply_jobs.application_id','apply_jobs.job_id','apply_jobs.user_id','advertisements.advertisement_no','recruiters.recruiter','jobtypes.jobtype','designations.designation','joborganisations.organisation','jobs.no_of_vacancy','jobs.description','jobs.opening_date','jobs.closing_date','joblocations.joblocation','users.fname','users.mname','users.lname','users.mobile','users.aadhar_no','users.registration_id', 'users_details.email1', 'users_details.email2', 'users_details.mobile1','users_details.father_name','users_details.father_contact','users_details.gender','users_details.pan_no','categories.cat_name','religions.religion_name','blood_groups.blood_group','languages.language','users_details.prefered_location1','users_details.prefered_location2','users_details.dob','users_details.passport_no','user_addresses.p_first_add','user_addresses.p_second_add','user_addresses.p_landmark','user_addresses.p_pincode','cities.name as p_city','states.name as p_state','user_addresses.c_first_add','user_addresses.c_second_add','user_addresses.c_landmark','user_addresses.c_pincode','c_cities.name as c_city_name','c_states.name as c_state_name','user_exps.latest_emp_cname','user_exps.latest_emp_from','user_exps.latest_emp_to','user_exps.prev_emp_cname','user_exps.prev_emp_from','user_exps.prev_emp_to','user_exps.total_exp_year','user_exps.total_exp_month','user_exps.relevant_exp_year','user_exps.relevant_exp_month','user_exps.current_salary_monthly','user_exps.home_salary_as_bank','users_qualifications.eight_school_name','users_qualifications.eight_passing_year','users_qualifications.eight_marks','users_qualifications.ten_board_name','users_qualifications.ten_passing_year','users_qualifications.ten_marks','users_qualifications.ten_stream','users_qualifications.twelve_board_name','users_qualifications.twelve_passing_year','users_qualifications.twelve_marks','users_qualifications.twelve_stream','users_qualifications.diploma_institute_name','users_qualifications.diploma_name','users_qualifications.diploma_passing_year','users_qualifications.diploma_marks','users_qualifications.diploma_stream','users_qualifications.ug_degree','users_qualifications.ug_branch','users_qualifications.ug_university','users_qualifications.ug_year','users_qualifications.ug_marks','users_qualifications.pg_degree','users_qualifications.pg_branch','users_qualifications.pg_university','users_qualifications.pg_year','users_qualifications.pg_marks','users_qualifications.pg_degree','users_qualifications.additional_institute_name','users_qualifications.additional_qual','users_qualifications.additional_qual_marks','users_qualifications.additional_qual_year','user_documents.photograph','user_documents.signature','user_documents.resume','user_documents.caste_certificate','user_edu_documents.ten_doc','user_edu_documents.twelve_doc','user_edu_documents.diploma_doc','user_edu_documents.ug_doc','user_edu_documents.pg_doc','user_edu_documents.add_cert_doc','payments.transaction_id','payments.pay_amount','payments.payment_date','payments.pay_mode','payments.pay_slip_attachment','payments.payment_status')
            ->where('apply_jobs.id',$id)
            ->first();
            $myArray = json_decode(json_encode($data), true);
             $dec_aadhar = decrypt($myArray['aadhar_no']);
             $data->dec_aadhar = $dec_aadhar;

            return response()->json([
            'data'=>$data
        ],200);
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
        //
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
}
