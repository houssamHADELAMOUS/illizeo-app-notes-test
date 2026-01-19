<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestTenantCreation extends Command
{
    protected $signature = 'test:create-tenant';
    protected $description = 'Test single tenant creation';

    public function handle(): int
    {
        $this->info('Creating test tenant...');

        $tenant = Tenant::create([
            'id' => Str::uuid()->toString(),
            'name' => 'Test Tenant',
            'email' => 'test@tenant.local',
        ]);

        $tenant->domains()->create([
            'domain' => 'test.localhost',
        ]);

        $this->info("Tenant created: {$tenant->id}");
        $this->info("Creating database...");

        try {
            if (method_exists($tenant, 'createDatabase')) {
                $tenant->createDatabase();
                $this->info("Database created successfully!");
            }
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }

        $this->info("Checking if database exists...");
        $result = \Illuminate\Support\Facades\DB::select("SHOW DATABASES LIKE 'tenant_%'");
        $this->info("Databases: " . json_encode($result));

        return 0;
    }
}
