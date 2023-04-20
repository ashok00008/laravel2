<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Result;
use App\Notice;
use App\Jobs;
use App\Advertisement;
use App\Designation;
use Auth;
use DB;

class ResultListController extends Controller
{ 
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
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

     public function notice()
    {
        $data = DB::table('jobnotices') 
            ->leftjoin('advertisements', 'advertisements.id', '=', 'jobnotices.adv_id')
            ->leftjoin('designations', 'designations.id', '=', 'jobnotices.designation_id')
            ->select('advertisements.advertisement_no','jobnotices.notice_attachment','jobnotices.notice_heading','jobnotices.id', 'designations.designation')
            ->get();
         return response()->json([
            'data'=>$data
        ],200);
    }

    public function noticeview($id)
    {
        $data = DB::table('jobnotices') 
            ->leftjoin('advertisements', 'advertisements.id', '=', 'jobnotices.adv_id')
            ->leftjoin('designations', 'designations.id', '=', 'jobnotices.designation_id')
            ->select('advertisements.advertisement_no','jobnotices.notice_attachment','jobnotices.notice_heading','jobnotices.id', 'designations.designation')
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
