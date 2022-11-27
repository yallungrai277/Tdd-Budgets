<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Api\Auth\LoginRequest;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\Api\Auth\RegisterRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $data = $request->validated();
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return response()->json(new UserResource($user));
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user->auth_token = $user->createToken('token')->plainTextToken;
        return response()->json(new UserResource($user));
    }

    public function me(Request $request)
    {
        $user = $request->user();
        return response()->json(new UserResource($user));
    }
}