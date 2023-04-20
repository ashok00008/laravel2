<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use Mail;
use App\Mail\AcceptPaymentMail;
use App\Mail\RejectPaymentMail;

class PaymentListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         $data = DB::table('payments') 
            ->leftjoin('apply_jobs', 'apply_jobs.id', '=', 'payments.apply_id')
            ->leftjoin('users', 'users.id', '=', 'payments.user_id')
            ->select('users.fname','users.mname','users.lname', 'apply_jobs.application_id','payments.transaction_id','payments.pay_amount','payments.payment_date','payments.pay_mode','payments.pay_slip_attachment','payments.payment_status','payments.id')
            ->where('payments.payment_status','0')
            ->get();
         return response()->json([
            'data'=>$data
        ],200);
    }

    public function processingpayment()
    {
         $data = DB::table('payments') 
            ->leftjoin('apply_jobs', 'apply_jobs.id', '=', 'payments.apply_id')
            ->leftjoin('users', 'users.id', '=', 'payments.user_id')
            ->select('users.fname','users.mname','users.lname', 'apply_jobs.application_id','payments.transaction_id','payments.pay_amount','payments.payment_date','payments.pay_mode','payments.pay_slip_attachment','payments.payment_status','payments.id')
            ->where('payments.payment_status','0')
            ->get();
         return response()->json([
            'data'=>$data
        ],200);
    }

    public function approvepayment()
    {
         $data = DB::table('payments') 
            ->leftjoin('apply_jobs', 'apply_jobs.id', '=', 'payments.apply_id')
            ->leftjoin('users', 'users.id', '=', 'payments.user_id')
            ->select('users.fname','users.mname','users.lname', 'apply_jobs.application_id','payments.transaction_id','payments.pay_amount','payments.payment_date','payments.pay_mode','payments.pay_slip_attachment','payments.payment_status','payments.id')
            ->where('payments.payment_status','1')
            ->get();
         return response()->json([
            'data'=>$data
        ],200);
    }

    public function rejectpayment()
    {
         $data = DB::table('payments') 
            ->leftjoin('apply_jobs', 'apply_jobs.id', '=', 'payments.apply_id')
            ->leftjoin('users', 'users.id', '=', 'payments.user_id')
            ->select('users.fname','users.mname','users.lname', 'apply_jobs.application_id','payments.transaction_id','payments.pay_amount','payments.payment_date','payments.pay_mode','payments.pay_slip_attachment','payments.payment_status','payments.id')
            ->where('payments.payment_status','2')
            ->get();
         return response()->json([
            'data'=>$data
        ],200);
    }

    public function accept($id)
    {
      $data=DB::table('payments')->where('id', $id)->update(array('payment_status' => '1'));

      $data1 = DB::table('payments')
       ->leftjoin('users', 'users.id', '=', 'payments.user_id')
       ->leftjoin('users_details', 'users_details.user_id', '=', 'payments.user_id')
       ->leftjoin('apply_jobs', 'apply_jobs.id', '=', 'payments.apply_id')
       ->select('users.id','users.fname', 'users.mobile', 'users_details.email1','apply_jobs.application_id')
       ->where('payments.id',$id)
       ->first();
       $myArray = json_decode(json_encode($data1), true);
        $to = $myArray['email1'];
       $name=$myArray['fname'];
        $mobile=$myArray['mobile'];
        $application_id=$myArray['application_id'];

        if($to!="")
        {
        Mail::to($to)->send(new AcceptPaymentMail($name,$application_id));
        }
 
        $api_key = '35CD26D870005C';
$contacts = $myArray['mobile'];
$from = 'BECILJ';
$msg="Dear ". $name." , You Payment for Job Application No.". $application_id." is Approved wait for interview confirmation"; 
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
            'data'=>$data
        ],200);   
    }

    public function reject($id)
    {
      $data=DB::table('payments')->where('id', $id)->update(array('payment_status' => '2'));
      $data1 = DB::table('payments')
       ->leftjoin('users', 'users.id', '=', 'payments.user_id')
       ->leftjoin('users_details', 'users_details.user_id', '=', 'payments.user_id')
       ->leftjoin('apply_jobs', 'apply_jobs.id', '=', 'payments.apply_id')
       ->select('users.id','users.fname', 'users.mobile', 'users_details.email1','apply_jobs.application_id')
       ->where('payments.id',$id)
       ->first();
       $myArray = json_decode(json_encode($data1), true);
        $to = $myArray['email1'];
        $subject="Payment Reject";
       $name=$myArray['fname'];
        $mobile=$myArray['mobile'];
        $application_id=$myArray['application_id'];

        if($to!="")
        {
        Mail::to($to)->send(new RejectPaymentMail($name,$application_id));
        }

        $api_key = '35CD26D870005C';
$contacts = $myArray['mobile'];
$from = 'BECILJ';
$msg="Dear ". $name." , You Payment for Job Application No.". $application_id." is Rejected Please Update your payment"; 
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
