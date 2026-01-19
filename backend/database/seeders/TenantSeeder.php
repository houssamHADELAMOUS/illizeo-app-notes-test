<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates test tenants for testing multi-tenancy functionality.
     */
    public function run(): void
    {
        $tenants = [
            [
                'name' => 'Acme Corporation',
                'email' => 'admin@acme.local',
                'domain' => 'acme.localhost',
            ],
            [
                'name' => 'Globex Corporation',
                'email' => 'admin@globex.local',
                'domain' => 'globex.localhost',
            ],
            [
                'name' => 'TechStart Inc',
                'email' => 'admin@techstart.local',
                'domain' => 'techstart.localhost',
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::firstOrCreate(
                ['email' => $tenantData['email']],
                [
                    'id' => Str::uuid()->toString(),
                    'name' => $tenantData['name'],
                ]
            );

            // Create domain if it doesn't exist
            $tenant->domains()->firstOrCreate(
                ['domain' => $tenantData['domain']]
            );

            // Create tenant database
            if (method_exists($tenant, 'createDatabase')) {
                try {
                    $tenant->createDatabase();
                    $this->command->info("Database created for tenant: {$tenant->name}");
                } catch (\Exception $e) {
                    $this->command->warn("Database already exists or error: {$e->getMessage()}");
                }
            }

            // Run migrations and seed tenant data
            $tenant->run(function () use ($tenant) {
                $this->command->call('migrate', [
                    '--path' => 'database/migrations/tenant',
                    '--database' => 'tenant',
                ]);

                $this->command->info("Migrations completed for tenant: {$tenant->name}");
            });
        }
    }
}
