<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Tenancy;

class TenantControllerFixed extends Controller
{
    /**
     * FIXED VERSION: Properly creates tenant database before running migrations
     */
    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'required|email|unique:tenants,email',
            'domain' => 'required|string|unique:domains,domain',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            // Step 1: Create tenant record in central database
            $tenant = Tenant::create([
                'id' => $this->generateTenantId($request->company_name),
                'name' => $request->company_name,
                'email' => $request->company_email,
            ]);

            // Step 2: Create domain association
            $tenant->domains()->create([
                'domain' => $request->domain,
            ]);

            // Step 3: EXPLICITLY CREATE THE TENANT DATABASE
            // This is the critical step the original code was missing!
            $tenantDbName = config('tenancy.database.prefix') . $tenant->id;
            $this->createTenantDatabase($tenantDbName);

            // Step 4: Now run migrations and seeders in tenant context
            $tenant->run(function () use ($request) {
                // Run tenant-specific migrations
                \Artisan::call('migrate', [
                    '--database' => 'tenant',
                    '--path' => 'database/migrations/tenant',
                    '--force' => true,
                ]);

                // Create admin user in tenant database
                \App\Models\User::create([
                    'name' => $request->admin_name,
                    'email' => $request->admin_email,
                    'password' => Hash::make($request->admin_password),
                    'role' => 'admin',
                ]);
            });

            return response()->json([
                'message' => 'Tenant created successfully',
                'tenant' => $tenant,
                'domain' => $request->domain,
                'database' => $tenantDbName,
            ], 201);

        } catch (\Exception $e) {
            // Clean up on failure
            if (isset($tenant)) {
                $tenant->delete();
            }

            return response()->json([
                'message' => 'Tenant creation failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * CRITICAL MISSING PIECE: Explicitly create the tenant database
     */
    private function createTenantDatabase(string $databaseName): void
    {
        // Get the connection config
        $connection = config('database.connections.mysql');

        try {
            // Create database using raw SQL
            \DB::connection('mysql')->statement(
                "CREATE DATABASE IF NOT EXISTS `$databaseName`
                 COLLATE '" . ($connection['collation'] ?? 'utf8mb4_0900_ai_ci') . "'"
            );
        } catch (\Exception $e) {
            throw new \Exception("Failed to create tenant database: " . $e->getMessage());
        }
    }

    private function generateTenantId(string $companyName): string
    {
        $id = strtolower(preg_replace('/[^a-z0-9]/', '_', $companyName));
        $id = preg_replace('/_+/', '_', $id);
        $id = trim($id, '_');

        // Make unique
        $originalId = $id;
        $counter = 1;

        while (Tenant::where('id', $id)->exists()) {
            $id = $originalId . '_' . $counter;
            $counter++;
        }

        return $id;
    }
}
