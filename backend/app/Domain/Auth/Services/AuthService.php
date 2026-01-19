<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\DTOs\AuthResponseDTO;
use App\Domain\Auth\DTOs\LoginDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
//    auth login method
    public function login(LoginDTO $dto): AuthResponseDTO
    {
        $user = User::where('email', $dto->email)->first();

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all existing tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('auth-token')->plainTextToken;

        return AuthResponseDTO::fromUser($user, $token);
    }

    /**
     * Get authenticated user data
     */
    public function getCurrentUser(User $user): AuthResponseDTO
    {
        return AuthResponseDTO::fromUser($user);
    }

    /**
     * Logout user (revoke current token)
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
