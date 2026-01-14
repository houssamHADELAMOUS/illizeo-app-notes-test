<?php

namespace App\Services;

use App\Repositories\NoteRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    protected $userRepository;
    protected $noteRepository;

    public function __construct(UserRepository $userRepository, NoteRepository $noteRepository)
    {
        $this->userRepository = $userRepository;
        $this->noteRepository = $noteRepository;
    }

    public function register(array $data): array
    {
        $user = $this->userRepository->create($data);
        $token = $user->createToken('API Token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function login(array $data): ?array
    {
        $user = $this->userRepository->findByEmail($data['email']);

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return null;
        }

        $token = $user->createToken('API Token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(): void
    {
        $user = Auth::user();
        if ($user) {
            // Clear user's cache on logout
            $this->noteRepository->clearUserCacheById($user->id, $user->tenant_id);
        }
        Auth::user()->currentAccessToken()->delete();
    }
}
