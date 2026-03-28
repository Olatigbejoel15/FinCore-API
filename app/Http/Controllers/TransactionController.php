<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    // Deposit money into authenticated user's account
    public function deposit(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string'
        ]);

        // Get the currently authenticated user
        $user = $request->user();

        // Increase the user's balance
        $user->balance += $request->amount;
        $user->save();

        // Save the transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Deposit made',
        ]);

        // Return success response
        return response()->json([
            'message' => 'Deposit successful',
            'balance' => $user->balance,
            'transaction' => $transaction
        ]);
    }

    // Withdraw money from authenticated user's account
    public function withdraw(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string'
        ]);

        // Get the currently authenticated user
        $user = $request->user();

        // Check if user has enough balance
        if ($user->balance < $request->amount) {
            return response()->json([
                'message' => 'Insufficient balance'
            ], 400);
        }

        // Reduce the user's balance
        $user->balance -= $request->amount;
        $user->save();

        // Save the transaction record
        $transaction = Transaction::create([
            'user_id' => $user->id,
            'type' => 'withdrawal',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Withdrawal made',
        ]);

        // Return success response
        return response()->json([
            'message' => 'Withdrawal successful',
            'balance' => $user->balance,
            'transaction' => $transaction
        ]);
    }

    // Fetch authenticated user's transaction history
    public function history(Request $request)
    {
        // Get the currently authenticated user
        $user = $request->user();

        // Fetch user's transactions, latest first
        $transactions = Transaction::where('user_id', $user->id)
            ->latest()
            ->get();

        // Return transaction history
        return response()->json([
            'transactions' => $transactions
        ]);
    }
}
