<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\InitializeTenancyByPath;

/*
|--------------------------------------------------------------------------
| Path-Based Tenant Routes
|--------------------------------------------------------------------------
|
| Access via: http://127.0.0.1:8000/{tenant-id}/api/auth/login
| Example: http://127.0.0.1:8000/acme/api/auth/login
|
| All routes here run in tenant context (tenant database is active).
|
*/

Route::middleware([
    'api',
    InitializeTenancyByPath::class,
])->group(function () {

    Route::prefix('api')->group(function () {
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
