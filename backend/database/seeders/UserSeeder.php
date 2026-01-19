<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates admin and regular users for testing.
     */
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);
        $this->command->info("Created admin user: admin@test.com / password123");

        // Create Regular Users
        $users = [
            [
                'name' => 'John Doe',
                'email' => 'john@test.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'user',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@test.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'user',
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@test.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'user',
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
            $this->command->info("Created user: {$userData['email']} / password123");
        }

        // Create additional random users (default role is 'user')
        User::factory(5)->create();
        $this->command->info("Created 5 additional random users with role: user");
    }
}
