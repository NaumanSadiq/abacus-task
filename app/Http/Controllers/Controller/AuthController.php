<?php

namespace App\Http\Controllers\Controller;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(Request $r): \Illuminate\Http\JsonResponse
    {
        $data = $r->validate([
            'name' => 'required', 'email' => 'required|email|unique:users', 'password' => 'required|min:6'
        ]);
        $user = User::create([
            'name' => $data['name'], 'email' => $data['email'], 'password' => bcrypt($data['password'])
        ]);
        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function login(Request $r): \Illuminate\Http\JsonResponse
    {
        $cred = $r->validate(['email' => 'required|email', 'password' => 'required']);
        if (!auth()->attempt($cred)) return response()->json(['message' => 'Invalid'], 401);
        $token = $r->user()->createToken('api')->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logout(Request $r): \Illuminate\Http\JsonResponse
    {
        $r->user()->currentAccessToken()->delete();
        auth()->logout();

        return response()->json(['message' => 'Logged out']);
    }
}
