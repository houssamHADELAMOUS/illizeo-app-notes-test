<?php

namespace App\Domain\Announcement\Actions;

use App\Domain\Announcement\DTOs\AnnouncementDTO;
use App\Domain\Announcement\DTOs\CreateAnnouncementDTO;
use App\Domain\Announcement\Services\AnnouncementService;

class CreateAnnouncementAction
{
    public function __construct(
        private AnnouncementService $announcementService
    ) {}

    public function execute(CreateAnnouncementDTO $dto): AnnouncementDTO
    {
        return $this->announcementService->createAnnouncement($dto);
    }
}
