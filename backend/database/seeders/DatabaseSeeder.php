<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database (central database).
     * Tenant databases are seeded automatically via TenancyServiceProvider when tenants are created.
     */
    public function run(): void
    {
        // Create tenants in central database
        // This will also create tenant databases, run migrations, and seed them automatically
        $this->call([
            TenantSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('=== All Seeding Complete ===');
        $this->command->info('');
        $this->command->info('Test Credentials (same for all tenants):');
        $this->command->info('  Admin: admin@test.com / password123');
        $this->command->info('  User:  john@test.com / password123');
        $this->command->info('  User:  jane@test.com / password123');
        $this->command->info('  User:  bob@test.com / password123');
        $this->command->info('');
        $this->command->info('Tenant Domains:');
        $this->command->info('  - acme.localhost');
        $this->command->info('  - techstart.localhost');
        $this->command->info('  - globex.localhost');
    }
}
