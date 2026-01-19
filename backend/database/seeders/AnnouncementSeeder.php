<?php

namespace Database\Seeders;

use App\Domain\Announcement\Enums\AnnouncementStatus;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates announcements for testing ownership and authorization.
     */
    public function run(): void
    {
        $admin = User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first();
        $users = User::whereHas('roles', fn($q) => $q->where('name', 'user'))->get();

        if (!$admin) {
            $this->command->error('Admin user not found. Run UserSeeder first.');
            return;
        }

        // Admin creates announcements (to test admin can edit/delete any)
        $adminAnnouncements = [
            [
                'title' => 'Welcome to the Team Board!',
                'content' => 'This is your new team collaboration space. Here you can share announcements, updates, and important information with your team members. Feel free to explore all the features available.',
                'status' => AnnouncementStatus::PUBLISHED->value,
                'user_id' => $admin->id,
            ],
            [
                'title' => 'System Maintenance Scheduled',
                'content' => 'We will be performing scheduled maintenance this weekend. Please save your work before Friday 6 PM. The system will be back online by Monday 8 AM.',
                'status' => AnnouncementStatus::PUBLISHED->value,
                'user_id' => $admin->id,
            ],
            [
                'title' => 'Draft: New Features Coming Soon',
                'content' => 'We are working on exciting new features including: real-time notifications, file attachments, and team mentions. Stay tuned for updates!',
                'status' => AnnouncementStatus::DRAFT->value,
                'user_id' => $admin->id,
            ],
        ];

        foreach ($adminAnnouncements as $data) {
            Announcement::create($data);
        }
        $this->command->info("Created 3 announcements by admin (2 published, 1 draft)");

        // Each regular user creates their own announcements
        foreach ($users->take(3) as $index => $user) {
            // Published announcement
            Announcement::create([
                'title' => "Team Update from {$user->name}",
                'content' => "Hello team! This is an update from {$user->name}. I wanted to share some progress on my current tasks. Everything is going well and I'm on track to meet our deadlines.",
                'status' => AnnouncementStatus::PUBLISHED->value,
                'user_id' => $user->id,
            ]);

            // Draft announcement (to test user can only edit their own drafts)
            Announcement::create([
                'title' => "Draft: {$user->name}'s Upcoming Project",
                'content' => "This is a draft announcement about an upcoming project. I will publish this once the details are finalized.",
                'status' => AnnouncementStatus::DRAFT->value,
                'user_id' => $user->id,
            ]);

            $this->command->info("Created 2 announcements by {$user->name} (1 published, 1 draft)");
        }

        // Create some random announcements using factory
        if ($users->count() > 0) {
            Announcement::factory(5)
                ->published()
                ->create([
                    'user_id' => $users->random()->id,
                ]);
            $this->command->info("Created 5 random published announcements");
        }

        $this->command->info("");
        $this->command->info("=== Test Scenarios ===");
        $this->command->info("1. Login as admin@test.com - Can edit/delete ANY announcement");
        $this->command->info("2. Login as john@test.com - Can only edit/delete OWN announcements");
        $this->command->info("3. Login as jane@test.com - Can only edit/delete OWN announcements");
        $this->command->info("4. All users can READ all published announcements");
        $this->command->info("5. Users can only see their OWN draft announcements");
    }
}
