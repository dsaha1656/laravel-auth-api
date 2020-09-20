<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private function sendResponse($status=0, $message="", $data=null){
        $data = json_encode(array('status'=>$status, 'message'=>$message, 'data'=>$data));
        return response($data)->header('Content-Type', 'application/json');
    }
    public function droplets(Request $request){
        return $this->sendResponse(0, "done continue");   
    }
}
