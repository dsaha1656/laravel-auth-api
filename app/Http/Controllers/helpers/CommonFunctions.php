<?php

namespace App\Http\Controllers\helpers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommonFunctions extends Controller
{
    public static function sendResponse($status=0, $message="", $data=null){
        $data = json_encode(array('status'=>$status, 'message'=>$message, 'data'=>$data));
        return response($data)->header('Content-Type', 'application/json');
    }
}
