<?php

namespace App\Http\Controllers\Autho;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\services\AuthService;
use app\Traits\ResponseTrait;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    
    function register(Request $request){
        try{
            $validateData=$request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role'     => 'required |in:user,admin',
            ]);
           $data=$request->only('name','email','password','role');
            $result=Authservice::registerservice($data);
            if(is_string($result)){
                return Response ::error($result,'user registration failed',409);
            }
            return Response ::success($result,'user registered successfully',201);
        }catch(Exception $e){
            return Response ::error($result,'user registered successfully',201);
        }
    }

    function login(Request $request){
        $request->validate([
            'email'=>'required|email',
            'password'=>'required|min:6'
        ]);
        $credentials = $request->only('email', 'password');
        $result=AuthService::login($credentials);
        if(is_string($result)){
            return Response::error($result,'login failed',401);
        }
        return Response::success($result,'login successful',200);
        
    }
    function logout(Request $request){
        Auth::logout();
        return Response::success(null,'logout successful',200);
    }

    function RefreshToken(Request $request){
        $newToken=Auth::refresh();
        return Response::success(['token'=>$newToken],'token refreshed successfully',200);
    }

}
