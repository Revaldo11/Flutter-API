<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'device_name' => 'required|string|max:20',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        return $user->createToken($request->device_name)->plainTextToken;
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'device_name' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        return User::create([
            'name' => $request->name,
            'email' => $request->email,
            'device_name' => $request->device_name,
            'password' => Hash::make($request->password),
        ]);
    }

    public function logout(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $user->tokens()->delete();
        }

        return response()->noContent();
    }
}
