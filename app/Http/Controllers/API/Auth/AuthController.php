<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function register(RegisterRequest $request)
    {
        $existingUser = User::where('email', '=', $request->email, false)->first();

        if ($existingUser) {
            return response()->json([
                'message' => 'الحساب موجود بالفعل'
            ], 409); // 409 Conflict
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'role' => $request->role ?? 'instructor',
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'User registered successfully',
            'user' => new UserResource($user),
            'token' => $token
        ], 201);
    }
    public function login(LoginRequest $request)
    {
        $user = User::where('email', '=', $request->email, false)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($user),
            'token' => $token
        ]);
    }
    public function logout(Request $request)
    {
        $request->user()->tokens()->where('id', $request->user()->currentAccessToken()->id)->delete();
        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }
    public function me(Request $request)
    {
        return response()->json([
            'user' => new UserResource($request->user())
        ]);
    }
}
