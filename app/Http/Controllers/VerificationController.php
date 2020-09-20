<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\VerifiesEmails;

use Illuminate\Auth\Events\Verified;
use App\Models\User;

class VerificationController extends Controller
{

    public function verify(Request $request, $id){
    
        $success_route = "/";
        
        $user = User::find($id);

        if(!$user){
            return redirect("/");
        }
        if ($user->hasVerifiedEmail()) {
            return redirect($success_route);
        }
        if ($user->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }
        return redirect($success_route);

    }
}