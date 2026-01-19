<?php

namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\AuthResponseDTO;
use App\Domain\Auth\DTOs\LoginDTO;
use App\Domain\Auth\Services\AuthService;

class LoginAction
{
    public function __construct(
        private AuthService $authService
    ) {}

    public function execute(LoginDTO $dto): AuthResponseDTO
    {
        return $this->authService->login($dto);
    }
}
