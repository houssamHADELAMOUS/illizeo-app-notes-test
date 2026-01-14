<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\CustomInitializeTenancyBySubdomain;

// Register route without tenancy (create new tenant)
Route::post('/register', [AuthController::class, 'register']);

// All other routes require tenancy
Route::middleware([CustomInitializeTenancyBySubdomain::class])->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('notes', NoteController::class);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});
