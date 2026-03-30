<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // 1️⃣ View all users
    public function allUsers()
    {
        $users = User::all(); // Fetch all users
        return UserResource::collection($users); // Return as JSON
    }

    // 2️⃣ Freeze or unfreeze a user account
    public function toggleFreeze($id)
    {
        $user = User::find($id); // Find user by ID

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->is_frozen = !$user->is_frozen; // Toggle frozen status
        $user->save(); // Save changes to database

        return response()->json([
            'message' => $user->is_frozen ? 'User account frozen' : 'User account unfrozen',
            'user' => new UserResource($user)
        ]);
    }

    // 3️⃣ Credit user manually
    public function creditUser(Request $request, $id)
    {
        // Validate request
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        $user->balance += $request->amount; // Add amount
        $user->save();

        // Record transaction
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'admin_credit',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Admin credited account',
        ]);

        return response()->json([
            'message' => 'User credited successfully',
            'user' => new UserResource($user)
        ]);
    }

    // 4️⃣ Debit user manually
    public function debitUser(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        $user = User::find($id);
        if (!$user) return response()->json(['message' => 'User not found'], 404);

        // Check balance
        if ($user->balance < $request->amount) {
            return response()->json(['message' => 'Insufficient balance'], 400);
        }

        $user->balance -= $request->amount; // Subtract amount
        $user->save();

        Transaction::create([
            'user_id' => $user->id,
            'type' => 'admin_debit',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Admin debited account',
        ]);

        return response()->json([
            'message' => 'User debited successfully',
            'user' => new UserResource($user)
        ]);
    }
}
