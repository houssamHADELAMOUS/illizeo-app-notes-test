<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;
use App\Models\Tenant;

class InitializeTenancyByPath
{
    /**
     * Handle an incoming request.
     *
     * Extract tenant from URL path: /{tenant-id}/api/...
     * Example: /1b927791-82a7-4358-92c7-77e4501737fe/api/auth/login
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the first segment of the path
        $pathSegments = array_filter(explode('/', trim($request->path(), '/')));

        if (empty($pathSegments)) {
            return response()->json(['message' => 'Tenant ID required'], 400);
        }

        $tenantIdentifier = array_values($pathSegments)[0];

        // Try to find tenant by ID or name
        $tenant = Tenant::where('id', $tenantIdentifier)
            ->orWhere('name', $tenantIdentifier)
            ->first();

        if (!$tenant) {
            return response()->json([
                'message' => "Tenant not found: {$tenantIdentifier}",
            ], 404);
        }

        // Initialize tenancy - this sets up the database connection
        Tenancy::initialize($tenant);
    }
}
