<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BloodGroup;
use Auth;

class BloodGroupController extends Controller
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
        $data = BloodGroup::all();
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
        $this->validate($request,[
            'blood_group'=>'required|min:2|max:10'
        ]);
       $bloodgroup = New BloodGroup();
       $bloodgroup->blood_group = $request->blood_group;
       $bloodgroup->created_by = Auth::user()->id;
       $bloodgroup->save();
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
        $data = BloodGroup::find($id);
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
       $this->validate($request,[
            'blood_group'=>'required|min:2|max:10'
        ]);
        $bloodgroup = BloodGroup::find($id);
        $bloodgroup->blood_group = $request->blood_group;
        $bloodgroup->created_by = Auth::user()->id;
        $bloodgroup->save();
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
        $bloodgroup = BloodGroup::find($id);
        $bloodgroup->delete();
    }
}
