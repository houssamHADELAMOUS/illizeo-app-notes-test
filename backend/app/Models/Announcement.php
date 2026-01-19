<?php

namespace App\Models;

use App\Domain\Announcement\Enums\AnnouncementStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'status',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => AnnouncementStatus::class,
        ];
    }

    /**
     * Get the user who created the announcement
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Alias for user relationship (for clarity)
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Scope for published announcements
     */
    public function scopePublished($query)
    {
        return $query->where('status', AnnouncementStatus::PUBLISHED);
    }

    /**
     * Scope for draft announcements
     */
    public function scopeDraft($query)
    {
        return $query->where('status', AnnouncementStatus::DRAFT);
    }

    /**
     * Check if announcement is published
     */
    public function isPublished(): bool
    {
        return $this->status === AnnouncementStatus::PUBLISHED;
    }

    /**
     * Check if announcement is draft
     */
    public function isDraft(): bool
    {
        return $this->status === AnnouncementStatus::DRAFT;
    }
}
