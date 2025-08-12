<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Register a new user
     *
     * @param array $data
     * @return array
     */
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at->format('Y-m-d H:i:s')
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Login user and generate token
     *
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        if (!auth()->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = auth()->user();
        $token = $user->createToken('api')->plainTextToken;

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'last_login' => now()->format('Y-m-d H:i:s')
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Logout user and invalidate token
     *
     * @param User $user
     * @return bool
     */
    public function logout(User $user): bool
    {
        $user->currentAccessToken()->delete();
        auth()->logout();
        
        return true;
    }
} 