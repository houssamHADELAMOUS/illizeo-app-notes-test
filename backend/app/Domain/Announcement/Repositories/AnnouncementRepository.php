<?php

namespace App\Domain\Announcement\Repositories;

use App\Domain\Announcement\Enums\AnnouncementStatus;
use App\Domain\Shared\Interfaces\RepositoryInterface;
use App\Domain\Shared\Services\CacheService;
use App\Models\Announcement;
use Illuminate\Database\Eloquent\Collection;

class AnnouncementRepository implements RepositoryInterface
{
    public function __construct(
        private Announcement $model
    ) {}

    public function all(): Collection
    {
        $cacheKey = CacheService::generateKey('announcements_all');

        return CacheService::remember(
            [CacheService::TAG_ANNOUNCEMENTS],
            $cacheKey,
            CacheService::TTL_MEDIUM,
            fn() => $this->model
                ->with('user:id,name')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function find(int $id): ?Announcement
    {
        $cacheKey = CacheService::generateKey('announcement', ['id' => $id]);

        return CacheService::remember(
            [CacheService::TAG_ANNOUNCEMENTS],
            $cacheKey,
            CacheService::TTL_LONG,
            fn() => $this->model->with('user:id,name')->find($id)
        );
    }

    public function findOrFail(int $id): Announcement
    {
        return $this->model->with('user:id,name')->findOrFail($id);
    }

    public function create(array $data): Announcement
    {
        $announcement = $this->model->create($data);

        // Invalidate cache
        CacheService::invalidate('announcement');

        return $announcement->load('user:id,name');
    }

    public function update(int $id, array $data): Announcement
    {
        $announcement = $this->findOrFail($id);
        $announcement->update($data);

        // Invalidate cache
        CacheService::invalidate('announcement');

        return $announcement->fresh()->load('user:id,name');
    }

    public function delete(int $id): bool
    {
        $result = $this->findOrFail($id)->delete();

        // Invalidate cache
        CacheService::invalidate('announcement');

        return $result;
    }

    public function findByUser(int $userId): Collection
    {
        $cacheKey = CacheService::generateKey('announcements_user', ['user_id' => $userId]);

        return CacheService::remember(
            [CacheService::TAG_ANNOUNCEMENTS],
            $cacheKey,
            CacheService::TTL_MEDIUM,
            fn() => $this->model
                ->with('user:id,name')
                ->where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function findByStatus(AnnouncementStatus $status): Collection
    {
        $cacheKey = CacheService::generateKey('announcements_status', ['status' => $status->value]);

        return CacheService::remember(
            [CacheService::TAG_ANNOUNCEMENTS],
            $cacheKey,
            CacheService::TTL_MEDIUM,
            fn() => $this->model
                ->with('user:id,name')
                ->where('status', $status)
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function findPublished(): Collection
    {
        return $this->findByStatus(AnnouncementStatus::PUBLISHED);
    }

    public function findDrafts(): Collection
    {
        return $this->findByStatus(AnnouncementStatus::DRAFT);
    }
}
