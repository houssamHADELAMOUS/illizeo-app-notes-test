<?php

namespace App\Domain\Announcement\Enums;

enum AnnouncementStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
