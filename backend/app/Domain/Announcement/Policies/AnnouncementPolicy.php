<?php

namespace App\Domain\Announcement\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{

    //  all authenticated users can view announcements.

    public function viewAny(User $user): bool
    {
        return true;
    }


    //  all authenticated users can view any announcement.

    public function view(User $user, Announcement $announcement): bool
    {
        return true;
    }


    //  all authenticated users can create announcements.

    public function create(User $user): bool
    {
        return true;
    }


    //  Only the author can update their own announcement.

    public function update(User $user, Announcement $announcement): bool
    {
        return $user->id === $announcement->user_id;
    }


    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->id === $announcement->user_id;
    }
}
