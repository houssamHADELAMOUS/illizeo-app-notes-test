<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant database.
     * Order matters: Users first, then Announcements.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AnnouncementSeeder::class,
        ]);
    }
}
