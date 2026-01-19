<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Services\AuthService;
use App\Models\User;

class LogoutAction
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function execute(User $user): void
    {
        $this->authService->logout($user);
    }
}
