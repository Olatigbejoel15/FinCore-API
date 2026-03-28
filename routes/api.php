<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']); // Register user
Route::post('/login', [AuthController::class, 'login']); // Login user

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']); // Get logged-in user info
    Route::post('/logout', [AuthController::class, 'logout']); // Logout user

    Route::post('/deposit', [TransactionController::class, 'deposit']); // Deposit funds
    Route::post('/withdraw', [TransactionController::class, 'withdraw']); // Withdraw funds
    Route::get('/transactions', [TransactionController::class, 'history']); // View transaction history
});
