<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\AuthResponseDTO;
use App\Domain\Auth\Services\AuthService;
use App\Models\User;

class GetCurrentUserAction
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function execute(User $user): AuthResponseDTO
    {
        return $this->authService->getCurrentUser($user);
    }
}
