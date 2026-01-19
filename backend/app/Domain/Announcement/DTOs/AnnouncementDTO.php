<?php

namespace App\Domain\Announcement\DTOs;

use App\Domain\Announcement\Enums\AnnouncementStatus;
use App\Models\Announcement;

class AnnouncementDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $content,
        public readonly string $status,
        public readonly string $statusLabel,
        public readonly int $userId,
        public readonly string $author,
        public readonly string $createdAt,
        public readonly string $updatedAt,
    ) {}

    public static function fromModel(Announcement $announcement): self
    {
        return new self(
            id: $announcement->id,
            title: $announcement->title,
            content: $announcement->content,
            status: $announcement->status->value,
            statusLabel: $announcement->status->label(),
            userId: $announcement->user_id,
            author: $announcement->user->name,
            createdAt: $announcement->created_at->toISOString(),
            updatedAt: $announcement->updated_at->toISOString(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'status_label' => $this->statusLabel,
            'user_id' => $this->userId,
            'author' => $this->author,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
