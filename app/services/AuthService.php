<?php

namespace App\Services;

use App\Models\User;
use App\Models\Admin;
use App\Models\HouseholderMember;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    public static function registerService(array $data)
    {
        $existUser = User::where('email', $data['email'])->first();

        if ($existUser) {
            $user = $existUser;

            // ADMIN
            if ($data['role'] === 'admin') {
                if (Admin::where('user_id', $user->id)->exists()) {
                    return "Admin already exists";
                }

                Admin::create(['user_id' => $user->id]);
            }

            // HOUSEHOLDER
            elseif ($data['role'] === 'householder') {
                if (HouseholderMember::where('user_id', $user->id)->exists()) {
                    return "Householder already exists";
                }

                HouseholderMember::create(['user_id' => $user->id]);
            }

            $token = Auth::login($user);
            $user->token = $token;

            return $user;
        }

        // Create new user
        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if ($data['role'] === 'admin') {
            Admin::create(['user_id' => $user->id]);
        } 
        elseif ($data['role'] === 'householder') {
            HouseholderMember::create(['user_id' => $user->id]);
        }

        $token = Auth::login($user);
        $user->token = $token;

        return $user;
    }

    public static function loginService(array $credentials)
    {
        if (!$token = Auth::attempt($credentials)) {
            return "Invalid credentials";
        }

        $user = Auth::user();
        $roles = [];

        if (Admin::where('user_id', $user->id)->exists()) {
            $roles[] = 'admin';
        } elseif (HouseholderMember::where('user_id', $user->id)->exists()) {
            $roles[] = 'householder';
        } else {
            return "User role not found";
        }

        $user->roles = $roles;
        $user->token = $token;

        return $user;
    }
}


// namespace App\Services;
// use App\Models\User;
// use App\Models\Admin;
// use Illuminate\Support\Facades\Hash;
// use Illuminate\Support\Facades\Auth;


// class AuthService
// {
//     /**
//      * Create a new class instance.
//      */
//     public function __construct()
//     {
//         //
//     }
   
//     public static function registerService(array $data)
//     {
//         // Check if user already exists
//         $existUser = User::where('email', $data['email'])->first();
    
//         if ($existUser) {
//             $userId = $existUser->id;
    
//             // Admin registration
//             if ($data['role'] === 'admin') {
//                 $existAdmin = Admin::where('user_id', $userId)->first();
//                 if ($existAdmin) {
//                     return "Admin already exists";
//                 }
    
//                 $admin = new Admin();
//                 $admin->user_id = $userId;
//                 $admin->save();
    
//                 $token = Auth::login($existUser);
//                 $existUser->token = $token;
    
//                 return $existUser;
//             }
    
//             // Householder registration
//             if ($data['role'] === 'householder') {
//                 $existHouseholder = HouseholderMember::where('user_id', $userId)->first();
//                 if ($existHouseholder) {
//                     return "Householder already exists";
//                 }
    
//                 $householder = new HouseholderMember();
//                 $householder->user_id = $userId;
//                 $householder->save();
    
//                 $token = Auth::login($existUser);
//                 $existUser->token = $token;
    
//                 return $existUser;
//             }
//         } else {
//             // Create new user
//             $user = new User();
//             $user->name = $data['name'];
//             $user->email = $data['email'];
//             $user->password = Hash::make($data['password']);
//             $user->save();
    
//             // Role assignment
//             if ($data['role'] === 'admin') {
//                 $admin = new Admin();
//                 $admin->user_id = $user->id;
//                 $admin->save();
//             } elseif ($data['role'] === 'householder') {
//                 $householder = new HouseholderMember();
//                 $householder->user_id = $user->id;
//                 $householder->save();
//             }
    
//             // Generate token for the user
//             $token = Auth::login($user);
//             $user->token = $token;
    
//             return $user;
//         }
//     }
//     public static function loginService(array $credentials)
//     {
//        if (!$token = Auth::attempt($credentials)) {
//            return "invalid credentials";
//         }
//         $user = Auth::user();
//         if (!$user) {
//             return "user not found";
//         }
//         $existAdmain = Admain::where('user_id',$user->id)->first();
//         $existHouseholder = HouseholderMember::where('user_id',$user->id)->first();
//         if($existAdmain){
//             $roles[]='admian';
//         }
//         else if($existHouseholder){
//         $roles[]='householder';
//         }
//         else{
//             return "user role not found";
//         }
//         $user->roles = $roles ;
//         $user->token = $token;
//         return $user;
        
        
//     }

// }
