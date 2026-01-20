<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// test auth
Route::middleware('tenant-sanctum')->get('/test-auth', [\App\Http\Controllers\Api\TestAuthController::class, 'test']);

Route::middleware('tenant-sanctum')->group(function () {
    // View all users (any authenticated user can view)
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{user}', [UserController::class, 'show']);
    // Create user (admin only)
    Route::post('/', [UserController::class, 'store'])->middleware('role:admin');
    // Delete user (admin only)
    Route::delete('/{user}', [UserController::class, 'destroy'])->middleware('role:admin');
});
