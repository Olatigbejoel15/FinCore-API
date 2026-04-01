<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Transaction;
// use Illuminate\Http\Request;

class AdminAnalyticsController extends Controller
{
    public function index()
    {
        // Total users
        $totalUsers = User::count();

        // Total frozen users
        $frozenUsers = User::where('is_frozen', true)->count();

        // Total deposits
        $totalDeposits = Transaction::where('type', 'deposit')->sum('amount');

        // Total withdrawals
        $totalWithdrawals = Transaction::where('type', 'withdraw')->sum('amount');

        // Total transfers sent
        $totalTransfersOut = Transaction::where('type', 'transfer_out')->sum('amount');

        // Total transfers received
        $totalTransfersIn = Transaction::where('type', 'transfer_in')->sum('amount');

        // System net balance
        $systemBalance = $totalDeposits + $totalTransfersIn - $totalWithdrawals - $totalTransfersOut;

        // Recent 10 transactions
        $recentTransactions = Transaction::latest()->take(10)->get();

        return response()->json([
            'total_users' => $totalUsers,
            'frozen_users' => $frozenUsers,
            'total_deposits' => $totalDeposits,
            'total_withdrawals' => $totalWithdrawals,
            'total_transfers_in' => $totalTransfersIn,
            'total_transfers_out' => $totalTransfersOut,
            'system_balance' => $systemBalance,
            'recent_transactions' => $recentTransactions
        ]);
    }
}
