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
        // Start query for authenticated user's transactions
        $query = Transaction::where('user_id', $request->user()->id);

        // Filter by transaction type if provided
        if ($request->has('type') && $request->type != '') {
            $query->where('type', $request->type);
        }

        // Search by description or type if provided
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', '%' . $search . '%')
                ->orWhere('type', 'like', '%' . $search . '%');
            });
        }

        // Get per_page from request or default to 3
        $perPage = $request->input('per_page', 3);

        // Order latest first and paginate
        $transactions = $query->latest()->paginate($perPage);

        // Return paginated transactions
        return response()->json($transactions);
    }
}
