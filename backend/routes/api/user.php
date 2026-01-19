<?php

use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Routes (Tenant Context)
|--------------------------------------------------------------------------
|
| Routes for user management within a tenant.
| All routes require authentication.
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // View all users (any authenticated user can view)
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{user}', [UserController::class, 'show']);
});
