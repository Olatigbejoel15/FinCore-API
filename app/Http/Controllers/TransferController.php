<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Transaction;

class TransferController extends Controller
{
    // Transfer money from authenticated user to another user
    public function transfer(Request $request)
    {
        // -------------------------------
        // Step 1: Validate incoming request
        // -------------------------------
        $request->validate([
            'recipient_email' => 'required|email',
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        // -------------------------------
        // Step 2: Get authenticated sender
        // -------------------------------
        $sender = $request->user();

        // -------------------------------
        // Step 3: Restriction check
        // -------------------------------
        if ($sender->is_frozen) {
            return response()->json([
                'message' => 'Account restricted. Contact your account officer.'
            ], 403);
        }

        // -------------------------------
        // Step 4: Prevent self-transfer
        // -------------------------------
        if ($sender->email === $request->recipient_email) {
            return response()->json([
                'message' => 'You cannot transfer money to your own account.'
            ], 400);
        }

        // -------------------------------
        // Step 5: Find recipient
        // -------------------------------
        $recipient = User::where('email', $request->recipient_email)->first();

        if (!$recipient) {
            return response()->json([
                'message' => 'Recipient not found.'
            ], 404);
        }

        // -------------------------------
        // Step 6: Check sender balance
        // -------------------------------
        if ($sender->balance < $request->amount) {
            return response()->json([
                'message' => 'Insufficient balance.'
            ], 400);
        }

        try {
            // -------------------------------
            // Step 7: Start database transaction
            // -------------------------------
            DB::transaction(function () use ($sender, $recipient, $request) {

                // Deduct from sender
                $sender->balance -= $request->amount;
                $sender->save();

                // Add to recipient
                $recipient->balance += $request->amount;
                $recipient->save();

                // Create notifications for sender
                $this->createNotification(
                    $sender->id,
                    'Transfer Sent',
                    'You sent ₦' . number_format($request->amount, 2) . ' to ' . $recipient->email
                );
                // Create notifications for recipient
                $this->createNotification(
                    $recipient->id,
                    'Transfer Received',
                    'You received ₦' . number_format($request->amount, 2) . ' from ' . $sender->email
                );

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
            });

            // -------------------------------
            // Step 8: Return success response
            // -------------------------------
            return response()->json([
                'message' => 'Transfer successful.',
                'sender_balance' => $sender->fresh()->balance,
                'recipient' => $recipient->email,
                'amount_sent' => $request->amount
            ], 200);

        } catch (\Exception $e) {
            // -------------------------------
            // Step 9: Catch unexpected errors
            // -------------------------------
            return response()->json([
                'message' => 'Transfer failed. Please try again later.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
