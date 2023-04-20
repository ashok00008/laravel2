<?php

namespace App\Http\Controllers;

use App\Jobs;
use Illuminate\Http\Request;

class SiteMapController extends Controller
{
    public function getSiteMap() {
        $urls = Jobs::select('id', 'created_at')->get();

        $data = $urls->map(function($q) {
            return $data = [
                'link' => url('/#/job-view') . '/' .$q->id,
                'created_at' => $q->created_at->format('Y-m-d\TH:i:sP'),
            ];
        });

        return \Response::view('sitemap', compact('data'))->header('Content-Type', 'application/xml'); 
    }
}
