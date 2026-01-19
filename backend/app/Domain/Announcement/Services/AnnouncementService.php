<?php

namespace App\Domain\Announcement\Services;

use App\Domain\Announcement\DTOs\AnnouncementDTO;
use App\Domain\Announcement\DTOs\CreateAnnouncementDTO;
use App\Domain\Announcement\DTOs\UpdateAnnouncementDTO;
use App\Domain\Announcement\Enums\AnnouncementStatus;
use App\Domain\Announcement\Repositories\AnnouncementRepository;
use App\Models\Announcement;

class AnnouncementService
{
    public function __construct(
        private AnnouncementRepository $announcementRepository
    ) {}

    /**
     * Get all announcements
     */
    public function getAllAnnouncements(): array
    {
        return $this->announcementRepository->all()
            ->map(fn(Announcement $announcement) => AnnouncementDTO::fromModel($announcement)->toArray())
            ->toArray();
    }

    /**
     * Get only published announcements
     */
    public function getPublishedAnnouncements(): array
    {
        return $this->announcementRepository->findByStatus(AnnouncementStatus::PUBLISHED)
            ->map(fn(Announcement $announcement) => AnnouncementDTO::fromModel($announcement)->toArray())
            ->toArray();
    }

    /**
     * Get announcement by ID
     */
    public function getAnnouncementById(int $id): AnnouncementDTO
    {
        $announcement = $this->announcementRepository->findOrFail($id);
        return AnnouncementDTO::fromModel($announcement);
    }

    /**
     * Create new announcement
     */
    public function createAnnouncement(CreateAnnouncementDTO $dto): AnnouncementDTO
    {
        $announcement = $this->announcementRepository->create([
            'title' => $dto->title,
            'content' => $dto->content,
            'status' => $dto->status->value,
            'user_id' => $dto->userId,
        ]);

        return AnnouncementDTO::fromModel($announcement);
    }

    /**
     * Update announcement
     */
    public function updateAnnouncement(int $id, UpdateAnnouncementDTO $dto): AnnouncementDTO
    {
        $announcement = $this->announcementRepository->update($id, $dto->toArray());
        return AnnouncementDTO::fromModel($announcement);
    }

    /**
     * Delete announcement
     */
    public function deleteAnnouncement(int $id): bool
    {
        return $this->announcementRepository->delete($id);
    }

    /**
     * Get announcement model for authorization
     */
    public function getAnnouncementModel(int $id): Announcement
    {
        return $this->announcementRepository->findOrFail($id);
    }
}
