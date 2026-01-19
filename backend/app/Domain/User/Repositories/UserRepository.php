<?php

namespace App\Domain\User\Repositories;

use App\Domain\Shared\Interfaces\RepositoryInterface;
use App\Domain\Shared\Services\CacheService;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements RepositoryInterface
{
    public function __construct(
        private User $model
    ) {}

    public function all(): Collection
    {
        $cacheKey = CacheService::generateKey('users_all');

        return CacheService::remember(
            [CacheService::TAG_USERS],
            $cacheKey,
            CacheService::TTL_MEDIUM,
            fn() => $this->model->with('roles')->get()
        );
    }

    public function find(int $id): ?User
    {
        $cacheKey = CacheService::generateKey('user', ['id' => $id]);

        return CacheService::remember(
            [CacheService::TAG_USERS],
            $cacheKey,
            CacheService::TTL_LONG,
            fn() => $this->model->with('roles')->find($id)
        );
    }

    public function findOrFail(int $id): User
    {
        return $this->model->with('roles')->findOrFail($id);
    }

    public function findByEmail(string $email): ?User
    {
        $cacheKey = CacheService::generateKey('user_email', ['email' => $email]);

        return CacheService::remember(
            [CacheService::TAG_USERS],
            $cacheKey,
            CacheService::TTL_LONG,
            fn() => $this->model->where('email', $email)->first()
        );
    }

    public function create(array $data): User
    {
        $user = $this->model->create($data);

        // Invalidate cache
        CacheService::invalidate('user');

        return $user;
    }

    public function update(int $id, array $data): User
    {
        $user = $this->findOrFail($id);
        $user->update($data);

        // Invalidate cache
        CacheService::invalidate('user');

        return $user->fresh();
    }

    public function delete(int $id): bool
    {
        $result = $this->findOrFail($id)->delete();

        // Invalidate cache
        CacheService::invalidate('user');

        return $result;
    }
}
