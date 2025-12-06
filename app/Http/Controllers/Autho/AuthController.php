<?php

namespace App\Http\Controllers\Autho;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AuthService;
use App\Traits\ResponseTrait;
use Exception;

class AuthController extends Controller
{
    use ResponseTrait;

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => 'required',
                'email'    => 'required|email|unique:users',
                'password' => 'required|min:6',
                'role'     => 'required|in:admin,householder',
            ]);

            $result = AuthService::registerService($validated);

            if (is_string($result)) {
                return $this->error($result, 'user registration failed', 409);
            }

            return $this->success($result, 'user registered successfully', 201);

        } catch (Exception $e) {
            return $this->error($e->getMessage(), 'server error', 500);
        }
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6'
        ]);

        $result = AuthService::loginService($validated);

        if (is_string($result)) {
            return $this->error($result, 'login failed', 401);
        }

        return $this->success($result, 'login successful', 200);
    }

    public function logout()
    {
        auth()->logout();
        return $this->success(null, 'logout successful', 200);
    }

    public function refreshToken()
    {
        $newToken = auth()->refresh();
        return $this->success(['token' => $newToken], 'token refreshed successfully', 200);
    }
}


// namespace App\Http\Controllers\Autho;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
// use App\services\AuthService;
// use app\Traits\ResponseTrait;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;


// class AuthController extends Controller
// {
    
//     function register(Request $request){
//         try{
//             $validateData=$request->validate([
//             'name'     => 'required',
//             'email'    => 'required|email|unique:users',
//             'password' => 'required|min:6',
//             'role'     => 'required |in:user,admin',
//             ]);
//            $data=$request->only('name','email','password','role');
//             $result=Authservice::registerservice($data);
//             if(is_string($result)){
//                 return Response ::error($result,'user registration failed',409);
//             }
//             return Response ::success($result,'user registered successfully',201);
//         }catch(Exception $e){
//             return Response ::error($result,'user registered successfully',201);
//         }
//     }

//     function login(Request $request){
//         $request->validate([
//             'email'=>'required|email',
//             'password'=>'required|min:6'
//         ]);
//         $credentials = $request->only('email', 'password');
//         $result=AuthService::login($credentials);
//         if(is_string($result)){
//             return Response::error($result,'login failed',401);
//         }
//         return Response::success($result,'login successful',200);
        
//     }
//     function logout(Request $request){
//         Auth::logout();
//         return Response::success(null,'logout successful',200);
//     }

//     function RefreshToken(Request $request){
//         $newToken=Auth::refresh();
//         return Response::success(['token'=>$newToken],'token refreshed successfully',200);
//     }

// }
