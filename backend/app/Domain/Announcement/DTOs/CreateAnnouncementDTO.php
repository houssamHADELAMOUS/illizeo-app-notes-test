<?php

namespace App\Domain\Announcement\DTOs;

use App\Domain\Announcement\Enums\AnnouncementStatus;

class CreateAnnouncementDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly int $userId,
        public readonly AnnouncementStatus $status = AnnouncementStatus::DRAFT,
    ) {}

    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            title: $data['title'],
            content: $data['content'],
            userId: $userId,
            status: isset($data['status'])
                ? AnnouncementStatus::from($data['status'])
                : AnnouncementStatus::DRAFT,
        );
    }
}
