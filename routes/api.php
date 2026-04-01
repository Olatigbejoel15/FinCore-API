<?php

use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StatementController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TransferController;
use App\Http\Middleware\AdminMiddleware;
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
    Route::post('/transfer', [TransferController::class, 'transfer']); // Transfer money to another user

    Route::get('/notifications', [NotificationController::class, 'index']);

});

Route::middleware(['auth:sanctum', AdminMiddleware::class])->prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'allUsers']);
    Route::post('/users/{id}/freeze', [AdminController::class, 'toggleFreeze']);
    Route::post('/users/{id}/credit', [AdminController::class, 'creditUser']);
    Route::post('/users/{id}/debit', [AdminController::class, 'debitUser']);

});

Route::middleware('auth:sanctum')->get('/statement', [StatementController::class, 'index']);
Route::middleware('auth:sanctum')->get('/admin/analytics', [AdminAnalyticsController::class, 'index']);

