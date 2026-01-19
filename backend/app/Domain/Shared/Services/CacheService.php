<?php

namespace App\Domain\Shared\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    // Cache duration constants (in seconds)
    public const TTL_SHORT = 300;              // 5 minutes
    public const TTL_MEDIUM = 1800;            // 30 minutes
    public const TTL_LONG = 3600;              // 1 hour
    public const TTL_VERY_LONG = 86400;        // 24 hours

    // Cache tag constants
    public const TAG_USERS = 'users';
    public const TAG_ANNOUNCEMENTS = 'announcements';
    public const TAG_ROLES = 'roles';
    public const TAG_PERMISSIONS = 'permissions';

    /**
     * Get cache tags that should be invalidated together for a given entity
     */
    public static function getInvalidationGroup(string $entity): array
    {
        return match ($entity) {
            'user' => [
                self::TAG_USERS,
            ],
            'announcement' => [
                self::TAG_ANNOUNCEMENTS,
            ],
            'role' => [
                self::TAG_ROLES,
                self::TAG_PERMISSIONS,
            ],
            'permission' => [
                self::TAG_PERMISSIONS,
            ],
            default => [],
        };
    }

    /**
     * Invalidate cache for a specific entity type
     */
    public static function invalidate(string $entity): void
    {
        $tags = self::getInvalidationGroup($entity);

        foreach ($tags as $tag) {
            Cache::tags([$tag])->flush();
        }
    }

    /**
     * Invalidate all application caches
     */
    public static function invalidateAll(): void
    {
        Cache::tags([
            self::TAG_USERS,
            self::TAG_ANNOUNCEMENTS,
            self::TAG_ROLES,
            self::TAG_PERMISSIONS,
        ])->flush();
    }

    /**
     * Generate cache key with consistent formatting
     */
    public static function generateKey(string $prefix, array $params = []): string
    {
        if (empty($params)) {
            return $prefix;
        }

        ksort($params);
        return $prefix . '_' . md5(json_encode($params));
    }

    /**
     * Remember with tags helper
     */
    public static function remember(array $tags, string $key, int $ttl, callable $callback)
    {
        return Cache::tags($tags)->remember($key, $ttl, $callback);
    }

    /**
     * Forget cache by tags
     */
    public static function forgetByTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }

    /**
     * Forget specific cache key
     */
    public static function forget(string $key): bool
    {
        return Cache::forget($key);
    }
}
