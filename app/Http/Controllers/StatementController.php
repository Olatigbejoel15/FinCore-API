<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class StatementController extends Controller
{
    public function index(Request $request)
    {
        // -------------------------------
        // Step 1: Validate dates
        // -------------------------------
        $request->validate([
            'from' => ['required', 'date', 'before_or_equal:to'],
            'to' => ['required', 'date'],
        ]);

        // -------------------------------
        // Step 2: Get logged-in user
        // -------------------------------
        $user = $request->user();

        // -------------------------------
        // Step 3: Get transactions in range
        // -------------------------------
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$request->from, $request->to])
            ->latest()
            ->get();

        // -------------------------------
        // Step 4: Calculate totals
        // -------------------------------
        $moneyInTypes = ['deposit', 'transfer_in', 'admin_credit'];
        $moneyOutTypes = ['withdraw', 'transfer_out', 'admin_debit'];

        $totalIn = $transactions
            ->whereIn('type', $moneyInTypes)
            ->sum('amount');

        $totalOut = $transactions
            ->whereIn('type', $moneyOutTypes)
            ->sum('amount');

        // -------------------------------
        // Step 5: Return response
        // -------------------------------
        return response()->json([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
            'from' => $request->from,
            'to' => $request->to,
            'total_in' => $totalIn,
            'total_out' => $totalOut,
            'net_balance_change' => $totalIn - $totalOut,
            'transactions' => $transactions
        ]);
    }
}
