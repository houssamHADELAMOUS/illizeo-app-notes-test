<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantAnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds for tenant announcements.
     * This should only be run within a tenant context.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please run TenantUserSeeder first.');
            return;
        }

        $announcements = [
            [
                'title' => 'Welcome to Our Platform',
                'content' => 'We are excited to announce the launch of our new platform. This will revolutionize how we communicate and collaborate.',
                'status' => 'published',
            ],
            [
                'title' => 'New Features Coming Soon',
                'content' => 'We are working on several exciting new features that will be released next month. Stay tuned!',
                'status' => 'draft',
            ],
            [
                'title' => 'System Maintenance Scheduled',
                'content' => 'Please be aware that our system will undergo maintenance on Saturday from 10 PM to 2 AM. We apologize for any inconvenience.',
                'status' => 'published',
            ],
            [
                'title' => 'Q1 2026 Results',
                'content' => 'We are pleased to report strong performance in Q1 2026. Thank you for your continued support.',
                'status' => 'published',
            ],
            [
                'title' => 'Training Session Available',
                'content' => 'New users can now attend our training session every Friday at 2 PM. Register now!',
                'status' => 'draft',
            ],
        ];

        foreach ($announcements as $announcementData) {
            Announcement::firstOrCreate(
                ['title' => $announcementData['title']],
                [
                    'content' => $announcementData['content'],
                    'status' => $announcementData['status'],
                    'user_id' => $users->random()->id,
                ]
            );
        }

        $this->command->info('Announcements seeded successfully!');
    }
}
