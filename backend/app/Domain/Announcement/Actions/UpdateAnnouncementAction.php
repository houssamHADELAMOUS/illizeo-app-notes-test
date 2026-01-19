<?php

namespace App\Domain\Announcement\Actions;

use App\Domain\Announcement\DTOs\AnnouncementDTO;
use App\Domain\Announcement\DTOs\UpdateAnnouncementDTO;
use App\Domain\Announcement\Services\AnnouncementService;

class UpdateAnnouncementAction
{
    public function __construct(
        private AnnouncementService $announcementService
    ) {}

    public function execute(int $id, UpdateAnnouncementDTO $dto): AnnouncementDTO
    {
        return $this->announcementService->updateAnnouncement($id, $dto);
    }
}
