<?php

namespace App\Domain\User\Actions;

use App\Domain\User\Services\UserService;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DeleteUserAction
{
    public function __construct(
        private UserService $userService
    ) {}

    public function execute(User $currentUser, int $targetUserId): bool
    {
        if (!$this->userService->canDelete($currentUser, $targetUserId)) {
            throw new HttpException(403, 'You cannot delete yourself');
        }

        return $this->userService->deleteUser($targetUserId);
    }
}
