<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::post('/register', [AuthController::class, 'register']); // Register user
Route::post('/login', [AuthController::class, 'login']); // Login user
