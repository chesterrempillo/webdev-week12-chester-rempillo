<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookController;

// Public authentication routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes - require Sanctum token
Route::middleware('auth:sanctum')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);

    // Create a read-only token (full-access token required)
    Route::post('/tokens/read-only', [AuthController::class, 'createReadOnlyToken'])
        ->middleware('abilities:books:write');

    // Read books
    Route::middleware('abilities:books:read')->group(function () {
        Route::get('/books', [BookController::class, 'index']);
        Route::get('/books/{id}', [BookController::class, 'show']);
    });

    // Create, update, and delete books
    Route::middleware('abilities:books:write')->group(function () {
        Route::post('/books', [BookController::class, 'store']);
        Route::put('/books/{id}', [BookController::class, 'update']);
        Route::patch('/books/{id}', [BookController::class, 'update']);
        Route::delete('/books/{id}', [BookController::class, 'destroy']);
    });
});
