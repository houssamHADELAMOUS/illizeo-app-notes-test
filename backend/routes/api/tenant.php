<?php

use App\Http\Controllers\Api\TenantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Registration Routes (Central App)
|--------------------------------------------------------------------------
|
| Routes for creating and managing tenants (companies).
| These run on the central application, not within tenant context.
|
*/

// Tenant registration (public - for creating new companies)
Route::post('/', [TenantController::class, 'store']);

// Tenant management
Route::get('/', [TenantController::class, 'index']);
Route::get('/{tenant}', [TenantController::class, 'show']);
Route::delete('/{tenant}', [TenantController::class, 'destroy']);
