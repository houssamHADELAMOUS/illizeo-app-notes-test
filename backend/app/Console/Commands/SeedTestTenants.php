<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SeedTestTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:seed-test {--fresh : Delete existing tenants and start fresh}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Create test tenants with databases and sample data';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting tenant seeding process...');
        $this->newLine();

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

        // Handle --fresh option
        if ($this->option('fresh')) {
            $this->info('Cleaning up existing tenants and databases...');
            $existingTenants = Tenant::all();

            foreach ($existingTenants as $existingTenant) {
                try {
                    // Delete will trigger TenantDeleted event which deletes the database
                    $existingTenant->delete();
                    $this->info("  ✓ Deleted tenant: {$existingTenant->name}");
                } catch (\Exception $e) {
                    $this->warn("  ⚠ Could not delete tenant: {$e->getMessage()}");
                }
            }

            // Run fresh migrations on central database
            $this->line('  ↳ Running fresh migrations on central database...');
            $this->call('migrate:fresh', [
                '--database' => 'central',
                '--quiet' => true,
            ]);
            $this->info("  ✓ Central database migrations completed");
            $this->newLine();
        }

        foreach ($tenants as $tenantData) {
            $this->line("Creating tenant: <fg=cyan>{$tenantData['name']}</>");

            try {
                // Create tenant - this will trigger TenantCreated event
                // which automatically: creates database, runs migrations, seeds database
                $tenant = Tenant::create([
                    'id' => Str::uuid()->toString(),
                    'name' => $tenantData['name'],
                    'email' => $tenantData['email'],
                ]);

                // Create domain
                $tenant->domains()->create([
                    'domain' => $tenantData['domain'],
                ]);

                $this->info("  ✓ Tenant created successfully!");
            } catch (\Exception $e) {
                $this->error("  ✗ Error creating tenant: {$e->getMessage()}");
                continue;
            }

            $this->newLine();
        }

        $this->info('All test tenants have been seeded!');
        $this->newLine();
        $this->table(
            ['Tenant Name', 'Email', 'Domain'],
            collect($tenants)->map(fn($t) => [
                $t['name'],
                $t['email'],
                $t['domain'],
            ])->toArray()
        );

        return self::SUCCESS;
    }
}

