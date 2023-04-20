<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Result;
use App\Jobs;
use App\Advertisement;
use App\Designation;
use Auth;
use DB;
use App\ExamResult;
use App\InterviewResult;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class ResultController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
         $data = DB::table('results') 
            ->leftjoin('advertisements', 'advertisements.id', '=', 'results.adv_id')
            ->leftjoin('designations', 'designations.id', '=', 'results.designation_id')
            ->select('advertisements.advertisement_no','results.result_attachment','results.id', 'designations.designation')
            ->get();
         return response()->json([
            'data'=>$data
        ],200);
    }

    public function store(Request $request)
    {
        $this->validate($request,[
             
        ]);

        $data = $request->result_attachment;
        $explode = explode(',',$data);
        $ex = explode('/',$data)[1];
        $extension = explode(';',$ex)[0];
        $valid_extention = ['pdf'];
        if(in_array($extension, $valid_extention)){
            $data = base64_decode($explode[1]);
            $filename = rand(10000000,999999999).".".$extension;
            $url = public_path().'/result/'.$filename;
            file_put_contents($url, $data);
            //return response()->json(['success'=>'successfully uploaded']);
        } else {
            return response()->json(['error'=>'please upload pdf file']);
        }

       $result = New Result();
       $result->adv_id = $request->adv_id;
       $result->designation_id = $request->designation_id;
       $result->result_attachment = $filename;
       $result->created_by = Auth::user()->id;
       $result->save();
        return response()->json([
            'message'=>'success'
        ],200);
    }

    public function destroy($id)
    {
        $jobnotice = Result::find($id);
        $jobnotice->delete();
    }

    public function getCandidateExamLists(Request $request)
    {
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
        $state_id = $request->state_id;
        $city_id = $request->city_id;
        $from = $request->from;
        $to = $request->to;
        $today = Carbon::today();
        
        $users = ExamResult::All('application_id');
    foreach ($users as $user) {
                $data[] = $user->application_id;
            }


        $applicationLists = DB::table('apply_jobs')
            ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('user_documents', 'user_documents.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->where('jobs.exam_conducted', 'Yes')
            ->whereNotIn('apply_jobs.application_id', $data)
            ->select('apply_jobs.application_id', 'advertisements.advertisement_no', 'users.fname',
            'users.lname', 'users.mobile', 'users_details.father_name', 'users_details.dob', 'users_details.email1', 'users_details.email2',
            'users_details.mobile1', 'user_exps.total_exp_year', 'user_exps.total_exp_month', 'categories.cat_name', 'designations.designation','user_documents.photograph');

        if($advertisement_id && $advertisement_id !='') {
            $applicationLists->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $applicationLists->where('jobs.designation_id', $position_id);
        }

        if($from && $from !='' && $to == '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $today));
        }

        if($from && $from !='' && $to != '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $to));
        }

        $applicationLists = $applicationLists->get();

        return response()->json(['status' => 'success', 'data' => $applicationLists], 200);
    }



    public function exportCandidateExamLists(Request $request)
    {
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
        $state_id = $request->state_id;
        $city_id = $request->city_id;
        $from = $request->from;
        $to = $request->to;
        $date = date('d-m-Y');
        $today = Carbon::today();
        
         $users = ExamResult::All('application_id');
    foreach ($users as $user) {
                $data[] = $user->application_id;
            }


        $applicationLists = DB::table('apply_jobs')
            ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('user_documents', 'user_documents.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->where('jobs.exam_conducted', 'Yes')
            ->whereNotIn('apply_jobs.application_id', $data)
            ->select('apply_jobs.application_id','apply_jobs.user_reg_id', 'advertisements.advertisement_no','designations.designation', 'users.fname', 'users.lname'
            );

        if($advertisement_id && $advertisement_id !='') {
            $applicationLists->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $applicationLists->where('jobs.designation_id', $position_id);
        }

        if($from && $from !='' && $to == '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $today));
        }

        if($from && $from !='' && $to != '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $to));
        }

        $applicationLists = $applicationLists->get();

        $applicationLists = $applicationLists->map(function($value) {
            return [
                'application_id' => $value->application_id,
                'advertisement_no'=> $value->advertisement_no,
                'designation' => $value->designation,
                'user_reg_id' => $value->user_reg_id,
                'name' => $value->fname . ' ' . $value->lname,
                'exam_status' => '',
                'marks' => ''
            ];
        });

         $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' .$date. '_' .'exam_candidate_lists.csv',
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];

        $list = collect($applicationLists)->map(function($x){ return (array) $x; })->toArray();

        # add headers for each column in the CSV download
        array_unshift($list, array_keys($list[0]));

        $callback = function() use ($list)
        {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return Response::stream($callback, 200, $headers);
    }


    public function getCandidateIvLists(Request $request)
    {
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
        $state_id = $request->state_id;
        $city_id = $request->city_id;
        $from = $request->from;
        $to = $request->to;
        $today = Carbon::today();
        
        $users = InterviewResult::All('application_id');
    foreach ($users as $user) {
                $data[] = $user->application_id;
            }


        $applicationLists = DB::table('apply_jobs')
            ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('exam_results', 'exam_results.application_id', 'apply_jobs.application_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('user_documents', 'user_documents.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->where('jobs.interview_conducted', 'Yes')
            ->whereNotIn('apply_jobs.application_id', $data)
            ->select('apply_jobs.application_id', 'advertisements.advertisement_no', 'users.fname',
            'users.lname', 'users.mobile', 'users_details.father_name', 'users_details.dob', 'users_details.email1', 'users_details.email2',
            'users_details.mobile1', 'user_exps.total_exp_year', 'user_exps.total_exp_month', 'categories.cat_name', 'designations.designation','user_documents.photograph',
            'jobs.exam_conducted','jobs.interview_conducted','exam_results.exam_status');

        if($advertisement_id && $advertisement_id !='') {
            $applicationLists->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $applicationLists->where('jobs.designation_id', $position_id);
        }

        if($from && $from !='' && $to == '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $today));
        }

        if($from && $from !='' && $to != '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $to));
        }

        $applicationLists = $applicationLists->get();
        
        $applicationLists = $applicationLists->map(function($value) {
            if(($value->exam_conducted === 'Yes' && $value->exam_status === 'pass')||($value->exam_conducted === 'No' && $value->interview_conducted === 'Yes')) {
                return $value;
            }
        });
        
        $applicationLists = $applicationLists->filter(function($value, $key) {
            return $value != null; 
        });
        
        

        return response()->json(['status' => 'success', 'data' => $applicationLists], 200);
    }

    public function exportCandidateIvLists(Request $request) 
    {
        $advertisement_id = $request->advertisement_id;
        $position_id = $request->position_id;
        $state_id = $request->state_id;
        $city_id = $request->city_id;
        $from = $request->from;
        $to = $request->to;
        $date = date('d-m-Y');
        $today = Carbon::today();
        
        $users = InterviewResult::All('application_id');
    foreach ($users as $user) {
                $data[] = $user->application_id;
            }


        $applicationLists = DB::table('apply_jobs')
            ->leftjoin('users', 'users.id', 'apply_jobs.user_id')
            ->leftjoin('jobs', 'jobs.id', 'apply_jobs.job_id')
            ->leftjoin('advertisements', 'advertisements.id', 'jobs.adv_id')
            ->leftjoin('users_details', 'users_details.user_id', 'users.id')
            ->leftjoin('categories', 'categories.id', 'users_details.category_id')
            ->leftjoin('exam_results', 'exam_results.application_id', 'apply_jobs.application_id')
            ->leftjoin('user_exps', 'user_exps.user_id', 'users.id')
            ->leftjoin('user_documents', 'user_documents.user_id', 'users.id')
            ->leftjoin('designations', 'designations.id', 'jobs.designation_id')
            ->where('jobs.interview_conducted', 'Yes')
            ->whereNotIn('apply_jobs.application_id', $data)
            ->select('apply_jobs.application_id','apply_jobs.user_reg_id', 'advertisements.advertisement_no','designations.designation', 'users.fname', 'users.lname','jobs.exam_conducted','jobs.interview_conducted','exam_results.exam_status'
            );

        if($advertisement_id && $advertisement_id !='') {
            $applicationLists->where('jobs.adv_id', $advertisement_id);
        }

        if($position_id && $position_id !='') {
            $applicationLists->where('jobs.designation_id', $position_id);
        }

        if($from && $from !='' && $to == '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $today));
        }

        if($from && $from !='' && $to != '') {
            $applicationLists->whereBetween(DB::raw('DATE(apply_jobs.created_at)'), array($from, $to));
        }

        $applicationLists = $applicationLists->get();
        
        $applicationLists = $applicationLists->map(function($value) {
            if(($value->exam_conducted === 'Yes' && $value->exam_status === 'pass')||($value->exam_conducted === 'No' && $value->interview_conducted === 'Yes')) {
                return $value;
            }
        });
        
        $applicationLists = $applicationLists->filter(function($value, $key) {
            return $value != null; 
        });

        $applicationLists = $applicationLists->map(function($value) {
            return [
                'application_id' => $value->application_id,
                'advertisement_no'=> $value->advertisement_no,
                'designation' => $value->designation,
                'user_reg_id' => $value->user_reg_id,
                'name' => $value->fname . ' ' . $value->lname,
                'interview_status' => '',
                'marks' => ''
            ];
        });

         $headers = [
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=' .$date. '_' .'exam_candidate_lists.csv',
            'Expires'             => '0',
            'Pragma'              => 'public'
        ];

        $list = collect($applicationLists)->map(function($x){ return (array) $x; })->toArray();

        # add headers for each column in the CSV download
        array_unshift($list, array_keys($list[0]));

        $callback = function() use ($list)
        {
            $FH = fopen('php://output', 'w');
            foreach ($list as $row) {
                fputcsv($FH, $row);
            }
            fclose($FH);
        };

        return Response::stream($callback, 200, $headers);
    }


    public function importCandidateExamResults(Request $request)
    {

        $imageName = time().'.'.$request->image->getClientOriginalExtension();

        $request->image->move(public_path('/documents/temp_res/'), $imageName);

             
        if (($handle = fopen ( public_path ('/documents/temp_res/') . $imageName, 'r' )) !== FALSE) {
            $counter=0;
            while ( ($data = fgetcsv ( $handle, 1000, ',' )) !== FALSE ) {
     
                //saving to db logic goes here
                if  ( !$counter == 0 ) 
                {
                    $requestData = [
                        'application_id' => $data [0],
                        'user_reg_id' => $data [3],
                        'exam_status' => $data [5],
                        'marks' => $data [6]
                    ];

                    $check = ExamResult::where('application_id',$data [0])->first();

                    if($check !== null)
                    {
                        $check->update($requestData);
                    }
                    else{
                      DB::table('exam_results')->insert($requestData);  
                    }
                     
                } 

                $counter++;
     
            }
            fclose ( $handle );
            unlink(public_path ('/documents/temp_res/') . $imageName);
        }

        return response()->json(['success'=>'You have successfully upload exam result.']);
        
       
    }

     public function importCandidateIvResults(Request $request)
    {

        $imageName = time().'.'.$request->image->getClientOriginalExtension();

        $request->image->move(public_path('/documents/temp_res/'), $imageName);

             
        if (($handle = fopen ( public_path ('/documents/temp_res/') . $imageName, 'r' )) !== FALSE) {
            $counter=0;
            $totalRecords = 0;
            $updateRecords = 0;
            while ( ($data = fgetcsv ( $handle, 1000, ',' )) !== FALSE ) {
     
                //saving to db logic goes here
                if  ( !$counter == 0 ) 
                {

                    $totalRecords = $counter;

                    $requestData = [
                        'application_id' => $data [0],
                        'user_reg_id' => $data [3],
                        'interview_status' => $data [5],
                        'marks' => $data [6]
                    ];

                    $check = InterviewResult::where('application_id',$data [0])->first();

                    if($check !== null)
                    {
                        $check->update($requestData);
                    }
                    else{
                      DB::table('interview_results')->insert($requestData);  
                    }
                     
                } 

                $counter++;
     
            }
            fclose ( $handle );
            unlink(public_path ('/documents/temp_res/') . $imageName);
        }

        return response()->json(['success'=>'You have successfully upload interview result.', 'total record' => $totalRecords]);
    }

}
