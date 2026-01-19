<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Routes (Tenant Context)
|--------------------------------------------------------------------------
|
| Routes that require admin role.
| All routes require authentication + admin role.
|
*/

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    // User management (admin only)
    Route::post('/users', [UserController::class, 'store']);
    Route::delete('/users/{user}', [UserController::class, 'destroy']);
});
