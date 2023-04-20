<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use Auth;
Use App\TempApplyJob;
Use App\Jobs;
use DB;
Use App\User;
Use App\ApplyJob;
use App\OnlinePayment;
use Razorpay\Api\Api;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    //private $razorpayId = "rzp_test_VYJCkYzL454Jtn";   //demotest
    //private $razorpayKey = "HMf78GeTDN6v9RcnYogK63Hk"; //demotest

    private $razorpayId = "rzp_live_s3Rgjbib9wtrHq";
     private $razorpayKey = "dzdydu9NpBhjYgB2P2VYqcQJ";

    public function index($id)
        {
            $job_id = $id;
            $userId =Auth::user()->id;

            $tempApply =TempApplyJob::all()
            ->where('job_id',$job_id)->where('user_id',$userId)->first();

            $Jobdata = DB::table('jobs') 
            ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
            ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
            ->leftjoin('recruiters', 'recruiters.id', '=', 'jobs.recruiter_id')
            ->leftjoin('jobtypes', 'jobtypes.id', '=', 'jobs.job_type_id')
            ->leftjoin('joblocations', 'joblocations.id', '=', 'jobs.location_id')
            ->leftjoin('fees', 'fees.id', '=', 'jobs.fee_sc_st_ph')
            ->leftjoin('fees as gen', 'gen.id', '=', 'jobs.fee_gen_obc')
            ->select('jobs.id as job_id','fees.fee as sc','gen.fee as general','joblocations.joblocation', 'designations.designation', 'jobtypes.jobtype', 'recruiters.recruiter','advertisements.advertisement_no')
            ->where('jobs.id',$job_id)
            ->first();

            $userData =DB::table('users_details')
            ->leftjoin('users', 'users.id', '=', 'users_details.user_id')
            ->leftjoin('categories', 'categories.id', '=', 'users_details.category_id')
            ->select('users_details.user_id as user_id','categories.cat_name','users_details.mobile1','users_details.email1','users.fname','users.lname')
            ->where('users_details.user_id',$userId)
            ->first();

            if($userData->cat_name=="General" || $userData->cat_name=="OBC")//match this with database uppercase lowercase both
            {
                $fee=$Jobdata->general;
            }

            else
            {
                $fee=$Jobdata->sc;
            }

           
            
            // Generate random receipt id
            $receiptId = Str::random(20);
        
            // Create an object of razorpay
            $api = new Api($this->razorpayId, $this->razorpayKey);
    
            // In razorpay you have to convert rupees into paise we multiply by 100
            // Currency will be INR
            // Creating order
            $order = $api->order->create(array(
                'receipt' => $receiptId,
                'amount' => $fee * 100,
                'currency' => 'INR'
                
                )
            );
            $response = [
                'orderId' => $order['id'],
                'razorpayId' => $this->razorpayId,
                'receipt' => $receiptId,
                'applicationId' => $tempApply->application_id,
                'registrationId' => $tempApply->user_reg_id,
                'fees' => $fee * 100, //// In razorpay you have to convert rupees into paise we multiply by 100
                'fname' => $userData->fname,
                'lname' => $userData->lname,
                'currency' => 'INR',
                'contactNumber' => $userData->mobile1,
                'joblocation' => $Jobdata->joblocation,
                'jobtype' => $Jobdata->jobtype,
                'email' => $userData->email1,
                'advertisement_no' => $Jobdata->advertisement_no,
                'recruiter' => $Jobdata->recruiter,
                'designation' => $Jobdata->designation,
                'category' => $userData->cat_name,
                'temp_apply_id'=>$tempApply->id,
           
            ];
    
            
    
            // Let's checkout payment page is it working
            //return view('payment-page',compact('response'));
            
            // return response(['tempApply_data'=>$tempApply,'job_data'=>$Jobdata,
            // 'userdata'=>$userData,'fee'=>$fee]);
            return response(['res_data'=>$response],200);
        }
       public function store(Request $request)
       {
        $userId = Auth::user()->id;
        $payment = new OnlinePayment();
        $payment->application_id = $request->application_id;
        $payment->payment_id = $request->payment_id;
        $payment->amount = $request->amount/100;
        $payment->job_id = $request->jobid;
        $payment->order_id = $request->orderId;
        $payment->user_id =$userId;
        $payment->temp_apply_id = $request->apply_id;
        $paymentstatus = $payment->save();
        
        if (!$paymentstatus) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong.'], 201);
        }
        //Insert in ApplyJob Table after Payment Success
        $userId = Auth::user()->id;
        $userDetails = User::where('id',$userId)->first();
        //dd($userDetails);
        $applyJob = new ApplyJob();
        $applyJob->application_id = $request->application_id;
        $applyJob->job_id = $request->jobid;
        $applyJob->user_id = $userId;
        $applyJob->user_reg_id = $userDetails->registration_id;
        $applyJob->save();
        //delete tempapplyjob data after success paymentstatus
        $tempApply =TempApplyJob::where('job_id',$request->jobid)->where('user_id',$userId)->delete();
        //close
        return response()->json(['status' => 'success', 'message' => 'Payment Done'], 200);
       }
       public function getReceiptData($id){
           $job_id = $id;
           $userId = Auth::user()->id;
           $receipt_data = DB::table('online_payments')
           ->leftjoin('jobs', 'jobs.id', '=', 'online_payments.job_id')
           ->leftjoin('advertisements', 'advertisements.id', '=', 'jobs.adv_id')
           ->leftjoin('designations', 'designations.id', '=', 'jobs.designation_id')
           ->select('online_payments.*','advertisements.advertisement_no','designations.designation')
           ->where('user_id',$userId)->where('job_id',$job_id)->first();
           return response()->json(['receipt_data' => $receipt_data],200);

       }
        
       
    
}
