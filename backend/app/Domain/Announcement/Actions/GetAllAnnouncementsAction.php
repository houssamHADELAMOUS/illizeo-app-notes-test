<?php

namespace App\Domain\Announcement\Actions;

use App\Domain\Announcement\Services\AnnouncementService;

class GetAllAnnouncementsAction
{
    public function __construct(
        private AnnouncementService $announcementService
    ) {}

    public function execute(): array
    {
        return $this->announcementService->getAllAnnouncements();
    }
}
