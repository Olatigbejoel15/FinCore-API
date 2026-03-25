<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Register new user
    public function register(Request $request)
    {
        // Validate incoming request data
        $request->validate([
            'name' => 'required|string|max:255', // Name is required
            'email' => 'required|email|unique:users', // Must be unique
            'password' => 'required|min:6' // Minimum password length
        ]);

        // Create new user in database
        $user = User::create([
            'name' => $request->name, // Get name from request
            'email' => $request->email, // Get email from request
            'password' => Hash::make($request->password), // Hash password securely
        ]);

        // Generate API token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return response
        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token
        ]);
    }

    // Login user
    public function login(Request $request)
    {
        // Validate login data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists AND password matches
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return success response
        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ]);
    }

        // Get authenticated user info
    public function user(Request $request)
    {
        return response()->json([
            'user' => $request->user() // $request->user() returns the logged-in user
        ]);
    }

    // Logout user
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful'
        ]);
    }
}
