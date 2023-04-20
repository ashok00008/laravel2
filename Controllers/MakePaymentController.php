<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ApplyJob;
use App\User;
use App\UsersDetails;
use App\Payment;
use Image;
use Auth;
use DB;
use Mail;
use App\Mail\PaymentMail;

class MakePaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $data = DB::table('apply_jobs') 
            ->leftjoin('users', 'users.id', '=', 'apply_jobs.user_id')
            ->leftjoin('users_details', 'users_details.user_id', '=', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', '=', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
            ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
            ->leftjoin('categories', 'categories.id', '=', 'users_details.category_id')
            ->leftjoin('fees as sc_st_ph_fee', 'sc_st_ph_fee.id', '=', 'jobs.fee_sc_st_ph')
            ->leftjoin('fees as gen_obc_fee', 'gen_obc_fee.id', '=', 'jobs.fee_gen_obc')
            ->select('apply_jobs.application_id','apply_jobs.user_reg_id','users.fname','users.mname', 'users.lname','designations.designation','categories.cat_name','sc_st_ph_fee.fee as sc_st_fee','gen_obc_fee.fee as gen_obc_fee','advertisements.advertisement_no','apply_jobs.id as applyid')
            ->where('apply_jobs.id',$id)
            ->first();

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
    public function storeneft(Request $request)
    {
        $strpos = strpos($request->slip,';');
            $sub = substr($request->slip,0,$strpos);
            $ex = explode('/',$sub)[1];
            $name = time().".".$ex;
            $img = Image::make($request->slip)->resize(500, 500);
            $upload_path = public_path()."/pay_slip/";
            $img->save($upload_path.$name);

        $payments = New Payment();
        $cat_name=$request->cat_name;
        if($cat_name=='Sc' || $cat_name=='St' || $cat_name=='Ph')
        {
            $amount=$request->sc_st_fee;
        }
        if($cat_name=='General' || $cat_name=='Obc')
        {
            $amount=$request->gen_obc_fee;
        }
       $payments->user_id = Auth::user()->id;
       $payments->apply_id = $request->applyid;
       $payments->transaction_id = $request->utr;
       $payments->pay_amount = $amount;
       $payments->pay_mode = 'NEFT/RTGS';
       $payments->pay_slip_attachment = $name;
       $payments->payment_date = $request->pay_date;
       $payments->payment_status = '0';
       $payments->save(); 

       $userid=Auth::user()->id;
       $data = DB::table('users')
       ->leftjoin('users_details', 'users_details.user_id', '=', 'users.id')
       ->select('users.id','users.fname','users.mname', 'users.lname', 'users.mobile','users.aadhar_no' ,'users.registration_id', 'users_details.email1')
       ->where('users.id',$userid)
       ->first();
       $myArray = json_decode(json_encode($data), true);
        $to = $myArray['email1'];
       $name = $myArray['fname'];
        $mobile=$myArray['mobile'];
        $application_id=$request->application_id;
        $designation=$request->designation;

         Mail::to($to)->send( new PaymentMail($name,$application_id,$designation,$amount));

        $api_key = '35CD26D870005C';
$contacts = $myArray['mobile'];
$from = 'BECILJ';
$msg="Dear ". $request->fname." , You Payment of ".$amount." INR for ".$designation." and Job Application No.". $application_id." is updated and Payment is under futher processing for verification"; 
$sms_text = urlencode($msg);



//Submit to server
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, "http://sms.sbcinfotech.com/app/smsapi/index.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=7246&routeid=100922&type=text&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text);
$response = curl_exec($ch);
curl_close($ch);
echo $response;

      
    }

    public function updateneft(Request $request, $id)
    {
        $strpos = strpos($request->slip,';');
            $sub = substr($request->slip,0,$strpos);
            $ex = explode('/',$sub)[1];
            $name = time().".".$ex;
            $img = Image::make($request->slip)->resize(500, 500);
            $upload_path = public_path()."/pay_slip/";
            $img->save($upload_path.$name);

            $payments = Payment::find($id);
        $cat_name=$request->cat_name;
        if($cat_name=='Sc' || $cat_name=='St' || $cat_name=='Ph')
        {
            $amount=$request->sc_st_fee;
        }
        if($cat_name=='General' || $cat_name=='Obc')
        {
            $amount=$request->gen_obc_fee;
        }
       $payments->user_id = Auth::user()->id;
       $payments->apply_id = $request->applyid;
       $payments->transaction_id = $request->utr;
       $payments->pay_amount = $amount;
       $payments->pay_mode = 'NEFT/RTGS';
       $payments->pay_slip_attachment = $name;
       $payments->payment_date = $request->pay_date;
       $payments->payment_status = '0';
       $payments->save();

       $userid=Auth::user()->id;
       $data = DB::table('users')
       ->leftjoin('users_details', 'users_details.user_id', '=', 'users.id')
       ->select('users.id','users.fname','users.mname', 'users.lname', 'users.mobile','users.aadhar_no' ,'users.registration_id', 'users_details.email1')
       ->where('users.id',$userid)
       ->first();
       $myArray = json_decode(json_encode($data), true);
        $to = $myArray['email1'];
       $name=$myArray['fname'];
        $mobile=$myArray['mobile'];
        $application_id=$request->application_id;
        $designation=$request->designation;

         Mail::to($to)->send(new PaymentMail($name,$application_id,$designation,$amount));

       $api_key = '35CD26D870005C';
$contacts = $myArray['mobile'];
$from = 'BECILJ';
$msg="Dear ". $name." , You Payment of ".$amount." INR for ".$designation." and Job Application No.". $application_id." is updated and Payment is under futher processing for verification"; 
$sms_text = urlencode($msg);



//Submit to server
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL, "http://sms.sbcinfotech.com/app/smsapi/index.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "key=".$api_key."&campaign=7246&routeid=100922&type=text&contacts=".$contacts."&senderid=".$from."&msg=".$sms_text);
$response = curl_exec($ch);
curl_close($ch);
echo $response;

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
    public function update($id)
    {
        $data = DB::table('payments') 
            ->leftjoin('apply_jobs', 'apply_jobs.id', '=', 'payments.apply_id')
            ->leftjoin('users', 'users.id', '=', 'apply_jobs.user_id')
            ->leftjoin('users_details', 'users_details.user_id', '=', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', '=', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
            ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
            ->leftjoin('categories', 'categories.id', '=', 'users_details.category_id')
            ->leftjoin('fees as sc_st_ph_fee', 'sc_st_ph_fee.id', '=', 'jobs.fee_sc_st_ph')
            ->leftjoin('fees as gen_obc_fee', 'gen_obc_fee.id', '=', 'jobs.fee_gen_obc')
            ->select('apply_jobs.application_id','apply_jobs.user_reg_id','users.fname','users.mname', 'users.lname','designations.designation','categories.cat_name','sc_st_ph_fee.fee as sc_st_fee','gen_obc_fee.fee as gen_obc_fee','advertisements.advertisement_no','apply_jobs.id as applyid')
            ->where('payments.id',$id)
            ->first();

             return response()->json([
            'data'=>$data
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
