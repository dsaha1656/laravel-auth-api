<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    private function sendResponse($status=0, $message="", $data=null){
        $data = json_encode(array('status'=>$status, 'message'=>$message, 'data'=>$data));
        return response($data)->header('Content-Type', 'application/json');
    }

    public function default(){
        return $this->sendResponse(0, "route not found");
    }

    public function resend(Request $request)
    {
        $email =  $request->get('email');
        if(!empty($email)){
            $users = User::where("email", $email)->get();
            if(count($users) > 0){
                $user = $users[0];
                $user->sendEmailVerificationNotification();
                return $this->sendResponse(1, "Verification mail Resend successfully");
            }else{
                return $this->sendResponse(0, "Email not found");
            }
        }else{
            return $this->sendResponse(0, "All Fields are required");
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
                    return $this->sendResponse(1, "User Registed Successfully");
                }else{
                    return $this->sendResponse(0, "Email Already Registered");
                }
            }else{
                return $this->sendResponse(0, "Passwords dont match");
            }
        }else{
            return $this->sendResponse(0, "All Fields are required");
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
                    return $this->sendResponse(1, "Login Success", $user);
                }else{
                    return $this->sendResponse(0, "Login Faild");
                }
            }else{
                return $this->sendResponse(0, "Email not found");
            }
        }else{
            return $this->sendResponse(0, "All Fields are required");
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
                if (($key = array_search($auth_header, $tokens)) !== false) {
                    unset($tokens[$key]);
                }
                $user->access_tokens = $tokens;
                $user->save();
            }
        }
        Auth::logout();
        return $this->sendResponse(0, "Logged out");
    }
}
