<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Image;
use App\UserDocument;
use App\UserEduDocument;
use App\User;
use DB;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;


class UserDocumentController extends Controller
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
        $file_nm=rand(10000000,999999999);

            
        if(isset($request->photograph))
        {
            $name = time().'.'.$request->photograph->getClientOriginalExtension();

        $request->photograph->move(public_path('/documents/profile_pic/'), $name);
        }
        else{
            $name='';
        }

            //user sign
        if(isset($request->signature))
        {
            $signame = time().'.'.$request->signature->getClientOriginalExtension();

        $request->signature->move(public_path('/documents/sign_pic/'), $signame);
        }
        else{
            $signame='';
        }

        //resume
        if(isset($request->resume))
        {
        $filename_resume = time().'.'.$request->resume->getClientOriginalExtension();

        $request->resume->move(public_path('/documents/resume/'), $filename_resume);

        }
        else{
            $filename_resume='';
        }

        if(isset($request->caste_certificate))
        {
        $filename_caste = time().'.'.$request->caste_certificate->getClientOriginalExtension();

        $request->caste_certificate->move(public_path('/documents/cast_cert/'), $filename_caste);

        }
        else{
            $filename_caste='';
        }

        if(isset($request->ten_doc))
        {
        $filename_ten_doc = time().'.'.$request->ten_doc->getClientOriginalExtension();

        $request->ten_doc->move(public_path('/documents/ten_doc/'), $filename_ten_doc);
        }
        else{
            $filename_ten_doc='';
        }

        if(isset($request->twelve_doc))
        {
        $filename_twelve_doc = time().'.'.$request->twelve_doc->getClientOriginalExtension();

        $request->twelve_doc->move(public_path('/documents/twelve_doc/'), $filename_twelve_doc);

        }
        else{
            $filename_twelve_doc='';
        }

        if(isset($request->diploma_doc))
        {
       $filename_diploma_doc = time().'.'.$request->diploma_doc->getClientOriginalExtension();

        $request->diploma_doc->move(public_path('/documents/diploma_doc/'), $filename_diploma_doc);
        }
        else{
            $filename_diploma_doc='';
        }

        if(isset($request->ug_doc))
        {
        $filename_ug_doc = time().'.'.$request->ug_doc->getClientOriginalExtension();

        $request->ug_doc->move(public_path('/documents/ug/'), $filename_ug_doc);
        }
        else{
            $filename_ug_doc='';
        }

        if(isset($request->pg_doc))
        {
        $filename_pg_doc = time().'.'.$request->pg_doc->getClientOriginalExtension();

        $request->pg_doc->move(public_path('/documents/pg/'), $filename_pg_doc);
        }
        else{
            $filename_pg_doc='';
        }

        if(isset($request->add_cert_doc))
        {
        $filename_add_cert_doc = time().'.'.$request->add_cert_doc->getClientOriginalExtension();

        $request->add_cert_doc->move(public_path('/documents/add_cert/'), $filename_add_cert_doc);
        }
        else{
            $filename_add_cert_doc='';
        }
        



       $userdocument = New UserDocument();
         $userdocument->user_id = Auth::user()->id;
         $userdocument->photograph = $name;
         $userdocument->signature = $signame;
         $userdocument->resume = $filename_resume;
         $userdocument->caste_certificate = $filename_caste;
         $userdocument->save();

       $useredudocument = New UserEduDocument();
       $useredudocument->user_id = Auth::user()->id;
       $useredudocument->ten_doc = $filename_ten_doc;
       $useredudocument->twelve_doc = $filename_twelve_doc;
       $useredudocument->diploma_doc = $filename_diploma_doc;
       $useredudocument->ug_doc = $filename_ug_doc;
       $useredudocument->pg_doc = $filename_pg_doc;
       $useredudocument->add_cert_doc = $filename_add_cert_doc;
       $useredudocument->save();

       $uid = $useredudocument->user_id;
       if($uid){
       $users = User::find($uid);
       $users->stage = '5'; 
       $users->save();
          
        }
        return response()->json([
            'status' => 'success', 'message'=>'success'
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
    public function edit()
    {
         $user_id=Auth::user()->id;
        $data = DB::table('users')  
            ->leftjoin('user_documents', 'user_documents.user_id', '=', 'users.id')
            ->leftjoin('user_edu_documents', 'user_edu_documents.user_id', '=', 'users.id')   
            ->select('user_documents.photograph','user_documents.signature','user_documents.resume','user_documents.caste_certificate','user_edu_documents.ten_doc','user_edu_documents.twelve_doc','user_edu_documents.diploma_doc','user_edu_documents.ug_doc','user_edu_documents.pg_doc','user_edu_documents.add_cert_doc')
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
    public function update(Request $request)
    {
        $valid_extention = ['pdf'];
        
        
        $user_id=Auth::user()->id;
        $data_d = DB::table('users')  
            ->leftjoin('user_documents', 'user_documents.user_id', '=', 'users.id')
            ->leftjoin('user_edu_documents', 'user_edu_documents.user_id', '=', 'users.id')   
            ->select('user_documents.photograph','user_documents.signature','user_documents.resume','user_documents.caste_certificate','user_edu_documents.ten_doc','user_edu_documents.twelve_doc','user_edu_documents.diploma_doc','user_edu_documents.ug_doc','user_edu_documents.pg_doc','user_edu_documents.add_cert_doc')
            ->where('users.id',$user_id)
            ->first();
        //user photo

            /*(in_array($request->photograph->getClientOriginalExtension(), $valid_extention)){
            
           return $request->photograph->getClientOriginalExtension(); 
        } else {
            return response()->json(['status' => 'error', 'message'=>'please upload jpg  file']);
        }*/
        if(isset($request->photograph))
        {
            $name = time().'.'.$request->photograph->getClientOriginalExtension();

        $request->photograph->move(public_path('/documents/profile_pic/'), $name);
        }
        else{
            $name=$data_d->photograph;
        }

            //user sign
        if(isset($request->signature))
        {
            $signame = time().'.'.$request->signature->getClientOriginalExtension();

        $request->signature->move(public_path('/documents/sign_pic/'), $signame);
        }
        else{
            $signame=$data_d->signature;
        }

        //resume
        if(isset($request->resume))
        {
        $filename_resume = time().'.'.$request->resume->getClientOriginalExtension();

        $request->resume->move(public_path('/documents/resume/'), $filename_resume);

        }
        else{
            $filename_resume=$data_d->resume;
        }

        if(isset($request->caste_certificate))
        {
        $filename_caste = time().'.'.$request->caste_certificate->getClientOriginalExtension();

        $request->caste_certificate->move(public_path('/documents/cast_cert/'), $filename_caste);

        }
        else{
            $filename_caste=$data_d->caste_certificate;
        }

        if(isset($request->ten_doc))
        {
        $filename_ten_doc = time().'.'.$request->ten_doc->getClientOriginalExtension();

        $request->ten_doc->move(public_path('/documents/ten_doc/'), $filename_ten_doc);
        }
        else{
            $filename_ten_doc=$data_d->ten_doc;
        }

        if(isset($request->twelve_doc))
        {
        $filename_twelve_doc = time().'.'.$request->twelve_doc->getClientOriginalExtension();

        $request->twelve_doc->move(public_path('/documents/twelve_doc/'), $filename_twelve_doc);

        }
        else{
            $filename_twelve_doc=$data_d->twelve_doc;
        }

        if(isset($request->diploma_doc))
        {
       $filename_diploma_doc = time().'.'.$request->diploma_doc->getClientOriginalExtension();

        $request->diploma_doc->move(public_path('/documents/diploma_doc/'), $filename_diploma_doc);
        }
        else{
            $filename_diploma_doc=$data_d->diploma_doc;
        }

        if(isset($request->ug_doc))
        {
        $filename_ug_doc = time().'.'.$request->ug_doc->getClientOriginalExtension();

        $request->ug_doc->move(public_path('/documents/ug/'), $filename_ug_doc);
        }
        else{
            $filename_ug_doc=$data_d->ug_doc;
        }

        if(isset($request->pg_doc))
        {
        $filename_pg_doc = time().'.'.$request->pg_doc->getClientOriginalExtension();

        $request->pg_doc->move(public_path('/documents/pg/'), $filename_pg_doc);
        }
        else{
            $filename_pg_doc=$data_d->pg_doc;
        }

        if(isset($request->add_cert_doc))
        {
        $filename_add_cert_doc = time().'.'.$request->add_cert_doc->getClientOriginalExtension();

        $request->add_cert_doc->move(public_path('/documents/add_cert/'), $filename_add_cert_doc);
        }
        else{
            $filename_add_cert_doc=$data_d->add_cert_doc;
        }

      

       $userdocument =  UserDocument::where('user_id', '=',  $user_id)->first();
         $userdocument->photograph = $name;
         $userdocument->signature = $signame;
         $userdocument->resume = $filename_resume;
         $userdocument->caste_certificate = $filename_caste;
         $userdocument->save();

       $useredudocument = UserEduDocument::where('user_id', '=',  $user_id)->first();
       $useredudocument->ten_doc = $filename_ten_doc;
       $useredudocument->twelve_doc = $filename_twelve_doc;
       $useredudocument->diploma_doc = $filename_diploma_doc;
       $useredudocument->ug_doc = $filename_ug_doc;
       $useredudocument->pg_doc = $filename_pg_doc;
       $useredudocument->add_cert_doc = $filename_add_cert_doc;
       $useredudocument->save();
 
       
         return response()->json([
            'status' => 'success', 'message'=>'success'
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
