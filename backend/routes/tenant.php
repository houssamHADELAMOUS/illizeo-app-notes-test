<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByPath;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| All routes here run in tenant context (tenant database is active).
|
| You can access these routes in two ways:
| 1. Domain-based: http://acme.localhost:8000/api/auth/login
| 2. Path-based: http://127.0.0.1:8000/{tenant-id}/api/auth/login
|
*/

// Web routes for tenant (domain-based)
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', function () {
        return 'This is your multi-tenant application. The id of the current tenant is ' . tenant('id');
    });
});

// API routes for tenant (domain-based)
Route::middleware([
    'api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api')->group(function () {

    // Authentication routes (login is public, me/logout require auth)
    Route::prefix('auth')->group(base_path('routes/api/auth.php'));

    // User routes (view users - authenticated users)
    Route::prefix('users')->group(base_path('routes/api/user.php'));

    // Admin routes (create/delete users - admin only)
    Route::group([], base_path('routes/api/admin.php'));

    // Announcement routes (all authenticated users, policy controls update/delete)
    Route::prefix('announcements')->group(base_path('routes/api/announcement.php'));
});

// Path-based tenant routes (for testing with 127.0.0.1)
// Access via: http://127.0.0.1:8000/{tenant-id}/api/auth/login
// Example: http://127.0.0.1:8000/1b927791-82a7-4358-92c7-77e4501737fe/api/auth/login
Route::prefix('{tenant_id}')->middleware([
    'api',
    \App\Http\Middleware\InitializeTenancyByPathMiddleware::class,
])->group(function () {
    Route::prefix('api')->group(function () {
        // Test endpoint
        Route::get('/test', [\App\Http\Controllers\Api\TenantTestController::class, 'test']);

        // Authentication routes (login is public, me/logout require auth)
        Route::prefix('auth')->group(base_path('routes/api/auth.php'));

        // User routes (view users - authenticated users)
        Route::prefix('users')->group(base_path('routes/api/user.php'));

        // Admin routes (create/delete users - admin only)
        Route::group([], base_path('routes/api/admin.php'));

        // Announcement routes (all authenticated users, policy controls update/delete)
        Route::prefix('announcements')->group(base_path('routes/api/announcement.php'));
    });
});

