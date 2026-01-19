<?php

namespace App\Domain\Announcement\DTOs;

use App\Domain\Announcement\Enums\AnnouncementStatus;

class UpdateAnnouncementDTO
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $content = null,
        public readonly ?AnnouncementStatus $status = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            content: $data['content'] ?? null,
            status: isset($data['status'])
                ? AnnouncementStatus::from($data['status'])
                : null,
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->title !== null) {
            $data['title'] = $this->title;
        }

        if ($this->content !== null) {
            $data['content'] = $this->content;
        }

        if ($this->status !== null) {
            $data['status'] = $this->status->value;
        }

        return $data;
    }
}
