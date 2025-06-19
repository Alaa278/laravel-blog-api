<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user'  => $user,
            'token' => $token,
        ], 201);
        
    }

public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (!$token =  JWTAuth::attempt($credentials)) {
        return response()->json([
            'error' => 'Invalid credentials'
        ], 401);
    }

    return response()->json([
        'message' => 'Login successful',
        'user'    =>  JWTAuth::user(),
        'token'   => $token
    ]);
}

}
