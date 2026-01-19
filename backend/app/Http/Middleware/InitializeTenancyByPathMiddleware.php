<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;
use App\Models\Tenant;

class InitializeTenancyByPathMiddleware
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

        // Try to find tenant by ID
        $tenant = Tenant::find($tenantIdentifier);

        if (!$tenant) {
            return response()->json([
                'message' => "Tenant not found: {$tenantIdentifier}",
            ], 404);
        }

        // Initialize tenancy and run the request within tenant context
        return $tenant->run(fn () => $next($request));
    }
}
