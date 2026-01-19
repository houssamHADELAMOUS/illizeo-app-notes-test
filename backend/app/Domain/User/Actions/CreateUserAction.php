<?php

namespace App\Domain\User\Actions;

use App\Domain\User\DTOs\CreateUserDTO;
use App\Domain\User\DTOs\UserDTO;
use App\Domain\User\Services\UserService;

class CreateUserAction
{
    public function __construct(
        private UserService $userService
    ) {}

    public function execute(CreateUserDTO $dto): UserDTO
    {
        return $this->userService->createUser($dto);
    }
}
