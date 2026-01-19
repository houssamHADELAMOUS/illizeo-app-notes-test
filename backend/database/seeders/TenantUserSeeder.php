<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantUserSeeder extends Seeder
{
    /**
     * Run the database seeds for tenant users.
     * This should only be run within a tenant context.
     */
    public function run(): void
    {
        // Create admin users
        User::firstOrCreate(
            ['email' => 'admin@tenant.local'],
            [
                'name' => 'Tenant Admin',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // Create regular users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@tenant.local',
                'role' => 'user',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@tenant.local',
                'role' => 'user',
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike@tenant.local',
                'role' => 'user',
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make('password123'),
                    'role' => $userData['role'],
                ]
            );
        }
    }
}
