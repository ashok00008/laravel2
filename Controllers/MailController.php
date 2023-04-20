<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use Illuminate\Suppoort\Facades\Mail;
use Mail;
use App\Mail\EmailSend;

class MailController extends Controller
{
    public function send()
    {
    	$name="Dipak";
    	$mobile="8423593896";
    	$to="dipakverma92@gmail.com";
    	Mail::to($to)->send(new EmailSend($name,$mobile));
    	return 'Email Send';
    }

}
