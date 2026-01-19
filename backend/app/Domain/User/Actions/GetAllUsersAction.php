<?php

namespace App\Domain\User\Actions;

use App\Domain\User\Services\UserService;

class GetAllUsersAction
{
    public function __construct(
        private UserService $userService
    ) {}

    public function execute(): array
    {
        return $this->userService->getAllUsers();
    }
}
