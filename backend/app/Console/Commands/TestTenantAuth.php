<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;

class TestTenantAuth extends Command
{
    protected $signature = 'test:tenant-auth {tenant_id}';
    protected $description = 'Test tenant authentication';

    public function handle()
    {
        $tenantId = $this->argument('tenant_id');
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("Tenant not found: $tenantId");
            return 1;
        }

        $this->info("Testing tenant: {$tenant->name}");

        // Initialize tenancy
        \Stancl\Tenancy\Facades\Tenancy::initialize($tenant);

        // Query users in tenant database
        $users = \App\Models\User::all();

        $this->info("Found " . $users->count() . " users in tenant database");

        $users->each(function ($user) {
            $this->line("  - {$user->name} ({$user->email}) - Role: {$user->role}");
        });

        return 0;
    }
}
