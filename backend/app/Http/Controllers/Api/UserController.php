<?php

namespace App\Http\Controllers\Api;

use App\Domain\User\Actions\CreateUserAction;
use App\Domain\User\Actions\DeleteUserAction;
use App\Domain\User\Actions\GetAllUsersAction;
use App\Domain\User\DTOs\CreateUserDTO;
use App\Domain\User\Services\UserService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function __construct(
        private GetAllUsersAction $getAllUsersAction,
        private CreateUserAction $createUserAction,
        private DeleteUserAction $deleteUserAction,
        private UserService $userService,
    ) {}

    /**
     * Display a listing of users.
     */
    public function index(): JsonResponse
    {
        $users = $this->getAllUsersAction->execute();

        return response()->json([
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role' => 'sometimes|string|in:admin,user',
        ]);

        $dto = CreateUserDTO::fromRequest($validated);
        $user = $this->createUserAction->execute($dto);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->toArray(),
        ], 201);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): JsonResponse
    {
        $userDTO = $this->userService->getUserById($user->id);

        return response()->json([
            'user' => $userDTO->toArray(),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->deleteUserAction->execute($request->user(), $user->id);

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
