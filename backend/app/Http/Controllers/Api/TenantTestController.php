<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TenantTestController extends Controller
{
    /**
     * Test endpoint to verify tenancy is working
     */
    public function test(): JsonResponse
    {
        $tenantId = tenant('id');
        $tenantName = tenant('name');

        // Try to fetch users from tenant database
        try {
            $users = \App\Models\User::all();
            $announcements = \App\Models\Announcement::all();

            return response()->json([
                'message' => 'Tenancy working correctly',
                'tenant_id' => $tenantId,
                'tenant_name' => $tenantName,
                'users_count' => $users->count(),
                'announcements_count' => $announcements->count(),
                'users' => $users->map(fn($u) => ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role]),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
            ], 500);
        }
    }
}
