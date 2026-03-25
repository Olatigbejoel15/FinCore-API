<?php

use Illuminate\Support\Facades\Route;

// First test route for FinCore API
Route::get('/test', function() {
    return response()->json([
        'message' => 'FinCore API is working!'
    ]);
});
