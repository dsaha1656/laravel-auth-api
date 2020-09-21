<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\helpers\CommonFunctions;

class DashboardController extends Controller
{
    public function droplets(Request $request){
        return CommonFunctions::sendResponse(0, "done continue");
        // return $this->sendResponse(0, "done continue");   
    }
}
