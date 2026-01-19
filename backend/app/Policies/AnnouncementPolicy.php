<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;

class AnnouncementPolicy
{
    /**
     * Determine whether the user can view any announcements.
     * All authenticated users can view announcements.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the announcement.
     * All authenticated users can view any announcement.
     */
    public function view(User $user, Announcement $announcement): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create announcements.
     * All authenticated users can create announcements.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the announcement.
     * Users can only update their own announcements.
     * Admins can update any announcement.
     */
    public function update(User $user, Announcement $announcement): bool
    {
        return $user->isAdmin() || $user->id === $announcement->user_id;
    }

    /**
     * Determine whether the user can delete the announcement.
     * Users can only delete their own announcements.
     * Admins can delete any announcement.
     */
    public function delete(User $user, Announcement $announcement): bool
    {
        return $user->isAdmin() || $user->id === $announcement->user_id;
    }
}
