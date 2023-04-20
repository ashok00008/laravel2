<?php

namespace App\Http\Controllers;

use App\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function index()
    {
        $states = State::select('id', 'name')->where(['status' => '1', 'country_id' => 101])->orderBy('name', 'ASC')->get();

        return response()->json(['status' => 'success', 'data' => $states], 200);
    }
}
