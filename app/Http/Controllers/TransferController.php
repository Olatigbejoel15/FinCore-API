<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Transaction;

class TransferController extends Controller
{
    // Transfer money from authenticated user to another user
    public function transfer(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'recipient_email' => 'required|email|exists:users,email',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        // Get the authenticated sender
        $sender = $request->user();

        // Restriction check: block transfer if account is frozen
        if ($sender->is_frozen) {
            return response()->json([
                'message' => 'Account restricted. Contact your account officer.'
            ], 403);
        }

        // Prevent user from sending money to themselves
        if ($sender->email === $request->recipient_email) {
            return response()->json([
                'message' => 'You cannot transfer money to your own account.'
            ], 400);
        }

        // Find the recipient by email
        $recipient = User::where('email', $request->recipient_email)->first();

        if (!$recipient) {
            // Return an error if the email is not in the database
            return response()->json([
                'success' => false,
                'message' => 'Recipient not found.'
            ], 404); // 404 Not Found
        }

        // Check if sender has enough balance
        if ($sender->balance < $request->amount) {
            return response()->json([
                'message' => 'Insufficient balance'
            ], 400);
        }

        // Deduct amount from sender
        $sender->balance -= $request->amount;
        $sender->save();

        // Add amount to recipient
        $recipient->balance += $request->amount;
        $recipient->save();

        // Save sender transaction
        Transaction::create([
            'user_id' => $sender->id,
            'type' => 'transfer_out',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Transfer sent to ' . $recipient->email,
        ]);

        // Save recipient transaction
        Transaction::create([
            'user_id' => $recipient->id,
            'type' => 'transfer_in',
            'amount' => $request->amount,
            'description' => $request->description ?? 'Transfer received from ' . $sender->email,
        ]);

        // Return success response
        return response()->json([
            'message' => 'Transfer successful',
            'sender_balance' => $sender->balance,
            'recipient' => $recipient->email,
            'amount_sent' => $request->amount
        ]);
    }
}
