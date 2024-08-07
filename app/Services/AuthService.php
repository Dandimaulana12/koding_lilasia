<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AuthService
{

    public function register(array $data): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|in:admin,user'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ]);

        return [
            'status' => true,
            'message' => 'User registered successfully',
            'data' => $user,
        ];
    }

    public function login(array $credentials): array
    {
        // Check if the user exists with the provided email
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            throw new BadRequestException('Email not found');
        }

        // Check if the provided password matches the user's password
        if (!Hash::check($credentials['password'], $user->password)) {
            throw new BadRequestException('Incorrect password');
        }

        // Create token
        $token = $user->createToken('API Token')->plainTextToken;

        return [
            'status' => true,
            'message' => 'Login successful',
            'data' => ['token' => $token],
        ];
    }
}
