<?php

namespace App\Http\Controllers;

use App\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $state_id = $request->state_id;

        $cities = City::select('id', 'name')->where('state_id', $state_id)->get();

        return response()->json(['status' => 'success', 'data' => $cities], 200);
    }
}
