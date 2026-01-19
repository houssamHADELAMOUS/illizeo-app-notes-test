<?php

namespace App\Domain\Announcement\Actions;

use App\Domain\Announcement\Services\AnnouncementService;

class DeleteAnnouncementAction
{
    public function __construct(
        private AnnouncementService $announcementService
    ) {}

    public function execute(int $id): bool
    {
        return $this->announcementService->deleteAnnouncement($id);
    }
}
