<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Illuminate\Mail\Message;
use Illuminate\Auth\Events\Verified;
use App\Http\Controllers\helpers\CommonFunctions;

class UserController extends Controller
{
    public function default(){
        return CommonFunctions::sendResponse(0, "route not found");
    }
    public function returnToFrontEnd()
    {
        die("will send to frontend");
    }
    public function resetPassword(Request $request){
        $password = $request->get('password');
        $token = $request->get('token');
        $password_confirmation = $request->get('password_confirmation');
        $email = $request->get('email');
        // $credentials = ['password'=>$password, 'password_confirmation'=>$password_confirmation, 'token'=>$token];
        if(!empty($password) && !empty($password_confirmation) && !empty($token) ){
            if($password == $password_confirmation){
                $users = User::where("email", $email)->get();
                if(count($users) > 0){
                    $user = $users[0];
                    if(Password::tokenExists($user, $token)){
                        $user->password = Hash::make($password);
                        $user->save();
                        Password::deleteToken($user);
                        return CommonFunctions::sendResponse(1, "Password Reset successfully");
                    }
                    return CommonFunctions::sendResponse(0, "Invalid Token");
                }else{
                    return CommonFunctions::sendResponse(0, "Email not found");
                }
                return CommonFunctions::sendResponse(1, "Password Reset Success", $user);
            }
            return CommonFunctions::sendResponse(0,"Password Dont Match");
        }
        return CommonFunctions::sendResponse(0,"All Data required");
    }
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
    public function reset(Request $request)
    {
        $email =  $request->get('email');
        if(!empty($email)){
            $users = User::where("email", $email)->get();
            if(count($users) > 0){
                $user = $users[0];
                $token = Password::getRepository()->create($user);
                $user->sendPasswordResetNotification($token);
                return CommonFunctions::sendResponse(1, "Password Reset Mail sent successfully");
            }else{
                return CommonFunctions::sendResponse(0, "Email not found");
            }
        }else{
            return CommonFunctions::sendResponse(0, "All Fields are required");
        }
    }
    public function resend(Request $request)
    {
        $email =  $request->get('email');
        if(!empty($email)){
            $users = User::where("email", $email)->get();
            if(count($users) > 0){
                $user = $users[0];
                $user->sendEmailVerificationNotification();
                return CommonFunctions::sendResponse(1, "Verification mail Resend successfully");
            }else{
                return CommonFunctions::sendResponse(0, "Email not found");
            }
        }else{
            return CommonFunctions::sendResponse(0, "All Fields are required");
        }
    }
    public function register(Request $request){
        $name = $request->get('name');
        $email = $request->get('email');
        $password = $request->get('password');
        $conf_password = $request->get('confirm_password');

        if(!empty($name) &&  !empty($email) && !empty($password)){
            if($password == $conf_password){
                $users = User::where("email", $email)->get();
                if(count($users) < 1){
                    $user = new User();
                    $user->name = $name;
                    $user->email = $email;
                    $user->password = Hash::make($password);
                    $user->save();
                    $user->sendEmailVerificationNotification();
                    return CommonFunctions::sendResponse(1, "User Registed Successfully");
                }else{
                    return CommonFunctions::sendResponse(0, "Email Already Registered");
                }
            }else{
                return CommonFunctions::sendResponse(0, "Passwords dont match");
            }
        }else{
            return CommonFunctions::sendResponse(0, "All Fields are required");
        }
    }
    public function login(Request $request){
        $email = $request->get('email');
        $password = $request->get('password');

        if(!empty($email) && !empty($password)){
            $users = User::where("email", $email)->get();
            if(count($users) > 0){
                if(Auth::attempt(['email' => $email, 'password' => $password])){
                    $user = Auth::user();
                    $token_key = Str::random(32);
                    $token = $user->id.":".$token_key;
                    $tokens = json_decode($user->access_tokens);
                    array_push($tokens, $token);
                    $user->access_tokens = $tokens;
                    $user->save();
                    return CommonFunctions::sendResponse(1, "Login Success", $user);
                }else{
                    return CommonFunctions::sendResponse(0, "Login Faild");
                }
            }else{
                return CommonFunctions::sendResponse(0, "Email not found");
            }
        }else{
            return CommonFunctions::sendResponse(0, "All Fields are required");
        }
    }
    public function logout(Request $request){
        $header = $request->header('Authorization');
        if (Str::startsWith($header, 'Bearer ')) {
            $auth_header = Str::substr($header, 7);
            $token = explode(':',$auth_header);
            $user = User::find($token[0]);
            
            if($user && (count($token) > 1) ){
                $tokens = json_decode($user->access_tokens);
                if(!is_array($tokens)){
                    $user->access_tokens = [];
                    $user->save();
                    Auth::logout();
                    return CommonFunctions::sendResponse(0, "Logged out", $user->access_tokens);
                }
                if (($key = array_search($auth_header, $tokens)) !== false) {
                    unset($tokens[$key]);
                    if(!is_array($tokens)){
                        $tokens = [];
                    }
                }
                $user->access_tokens = $tokens;
                $user->save();
            }
        }
        Auth::logout();
        return CommonFunctions::sendResponse(0, "Logged out");
    }
}
