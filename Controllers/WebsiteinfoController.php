<?php

namespace App\Http\Controllers;
use DB;

use Illuminate\Http\Request;

class WebsiteinfoController extends Controller
{
    public function footerinfo()
    {
    	$data = DB::table('website_info')     
            ->select('page','title','corp_add','head_add')
            ->where('page','footer')
            ->first();
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function homeinfo()
    {
    	$data = DB::table('website_info')     
            ->select('page','title','notice','banner','page_content')
            ->where('page','home')
            ->first();
        return response()->json([
            'data'=>$data
        ],200);
    }

    public function aboutusinfo()
    {
    	$data = DB::table('website_info')     
            ->select('page','title','page_content')
            ->where('page','aboutus')
            ->first();
        return response()->json([
            'data'=>$data
        ],200);
    }

    
    public function termconditioninfo()
    {
    	$data = DB::table('website_info')     
            ->select('page','title','page_content')
            ->where('page','term-condition')
            ->first();
        return response()->json([
            'data'=>$data
        ],200);
    }


    public function privacy_policy_info()
    {
    	$data = DB::table('website_info')     
            ->select('page','title','page_content')
            ->where('page','privacy-policy')
            ->first();
        return response()->json([
            'data'=>$data
        ],200);
    }
}
