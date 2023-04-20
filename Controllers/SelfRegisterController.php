<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\SelfRegister;
use App\District;
use Illuminate\Support\Facades\DB;
use Mail;
use Illuminate\Support\Facades\Response;

class SelfRegisterController extends Controller
{

    public function candidateSelfRegistration(){
        $state = DB::table('states')
        ->where('country_id', '101')
        ->get();
        $qualifications = DB::table('qualifications')->get();
        return view('public.create_self_register', compact('state', 'qualifications'));

    }

    public function candidateSelfRegistrationRedirect(){
        return redirect()->route('online-registration');
    }

    public function postCandidateSelfRegistration(Request $req){
        $req->validate([
            'email' => 'required|unique:self_registers',
        ], 
             [
            'email.unique' => 'You have already registered!',
          ]
        );

        //Unique Self Registration Code
        $total_rows = SelfRegister::orderBy('id', 'desc')->count();
        $self_code = "SELF/";

        if($total_rows==0){
            $self_code .= '0001';
        }else{
            $last_id = SelfRegister::orderBy('id', 'desc')->first()->id;
            $self_code .= sprintf("%'04d",$last_id + 1);
        }

        $file_name = null;
        if($req->hasfile('resume_file'))
         {
            $file = $req->file('resume_file');
            $file_name = time().'.'.$file->extension();
            // $file->move(base_path() . '/self_register', $file_name);
            $file->move(public_path('/self_register') , $file_name);
         }

       $self = new SelfRegister();
       $self->self_code = $self_code;
       $self->name = $req->name;
       $self->state = $req->state;
       $self->district = $req->district;
       $self->city = $req->city;
       $self->gender = $req->gender;
       $self->pincode = $req->pincode;
       $self->category = $req->category;
       $self->contact = $req->contact;
       $self->email = $req->email;
       $self->qualification = $req->qualification;
       $self->address = $req->address;
       $self->resume = $file_name;
       $self->save();

    //    $self_data = [
    //     'name' => $req->name,
    //     'id' => $self->id
    //    ];

    //    $toEmail = 'ankit.bisht@prakharsoftwares.com';
    //    Mail::send('SendMail.Online-Registration.registration', $self_data, function ($message) use ($toEmail) {
    //     $message->from(env('MAIL_USERNAME'));
    //     $message->to($toEmail)->subject('Online Candidate Registration');
    //     });

       return redirect()->back()->with('alert_success','You have been registered successfully. We will contact you very soon!');
    }

    public function editCandidateSelfRegistration(Request $req){
        $state = DB::table('states')
        ->where('country_id', '101')
        ->get();
        $qualifications = DB::table('qualifications')->get();
        $self_data = SelfRegister::with('getStateName','getDistrictName')->where('id', $req->id)->get(); 
        return view('public.edit_self_register', compact(['state', 'qualifications', 'self_data']));
    }

    public function updateCandidateSelfRegistration(Request $req){
        // $req->validate([
        //     'email' => 'required|unique:self_registers',
        // ], 
        //      [
        //     'email.unique' => 'You have already registered!',
        //   ]
        // );
        $file_name = $req->resume;
        if($req->hasfile('resume_file'))
         {
            $file = $req->file('resume_file');
            $file_name = time().'.'.$file->extension();
            // $file->move(base_path() . '/self_register', $file_name);
            $file->move(public_path('/self_register') , $file_name);
         }

        SelfRegister::where('id',$req->id)->update([
        'self_code' => $req->self_code,
        'name' => $req->name,
        'state' => $req->state,
        'district' => $req->district,
        'city' => $req->city,
        'gender' => $req->gender,
        'pincode' => $req->pincode,
        'category' => $req->category,
        'contact' => $req->contact,
        'email' => $req->email,
        'qualification' => $req->qualification,
        'address' => $req->address,
        'resume' => $file_name
         ]);

        //    $self_data = [
        //     'name' => $req->name,
        //     'id' => $self->id
        //    ];
        //    return view('SendMail.Online-Registration.registration', compact('self_data'));

        //    $toEmail = 'ankit.bisht@prakharsoftwares.com';
        //    Mail::send('SendMail.Online-Registration.registration', $self_data, function ($message) use ($toEmail) {
        //     $message->from(env('MAIL_USERNAME'));
        //     $message->to($toEmail)->subject('Online Candidate Registration');
        // });
        // $self = SelfRegister::with('getStateName','getDistrictName')->where("id", $req->id)->first(); 
       return redirect('candidate_self_registration_details/'.$req->id);
    }

    public function candidateSelfRegistrationDetails($id){
        $self = SelfRegister::with('getStateName','getDistrictName')->where("id", $id)->first(); 
        return view('public.self_register_detail',compact('self'));
    }

    public function candidateSelfRegistrationList(){
        $self_data = SelfRegister::with('getStateName','getDistrictName')->orderByDesc("id")->get(); 
        return response()->json(['data' => $self_data], 200);
        // return view('public.self_register_list', compact('self_data'));
    }

    public function fetchDistrict(Request $request)
    {
        $data = District::where("state_id",$request->state_id)->get(["district_name", "id"]);
        return response()->json($data);
    }

    public function exportCheckedOnlineRegistration($id)
    {
        $ids = explode(',', $id);
        $headers = [
                'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
                'Content-type'        => 'text/csv',
                'Content-Disposition' => 'attachment; filename=online-registration-candidates.csv',
                'Expires'             => '0',
                'Pragma'              => 'public'
            ];
    
            $list = SelfRegister::with('getStateName','getDistrictName')
                    ->orderByDesc("id")
                    ->whereIn('id', $ids)
                    ->get(); 
                
            $list = collect($list)->map(function ($x)  {
                return [
                    'Registration Code' => $x->self_code,
                    'Name' => $x->name,
                    'Email' => $x->email,
                    'Contact' => $x->contact ? $x->contact:'Not Specified',
                    'Category' => $x->category,
                    'Gender' => $x->gender,
                    'Qualification' => $x->qualification,
                    // 'State' => $x->get_state_name.states_name,
                    // 'District' => $x->get_district_name.district_name,
                    'City' => $x->city ? $x->city:'Not Specified',
                    'Pincode' => $x->pincode ? $x->pincode:'Not Specified',
                    'Address' => $x->address ? $x->address:'Not Specified',
                    'Resume' => $x->resume ? url('self_register/' . $x->resume) : 'Not Available',
                    'Creation Date' => $x->created_on
                ];
            })->toArray();

    
            # add headers for each column in the CSV download
            array_unshift($list, array_keys($list[0]));
            
    
            $callback = function () use ($list) {
                $FH = fopen('php://output', 'w');
                foreach ($list as $row) {
                    fputcsv($FH, $row);
                }
                fclose($FH);
            };
            // dd($callback);
    
            return Response::stream($callback, 200, $headers);
    }

}
