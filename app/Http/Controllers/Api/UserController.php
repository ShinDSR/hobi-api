<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\CreateUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function list(): JsonResponse
    {
        $users = $this->userService->getAll();
        
        return response()->json([
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function create(CreateUserRequest $request): UserResource
    {
        $user = $this->userService->create($request->validated());
        return new UserResource($user);
    }

    public function getByID(string $id): UserResource|JsonResponse
    {
        $user = $this->userService->getById($id);

        if (!$user) {
            return response()->json(['errors' => 'User not found'], 404);
        }

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, string $id): UserResource|JsonResponse
    {
        $user = $this->userService->update($id, $request->validated());

        if (!$user) {
            return response()->json(['errors' => 'User not found'], 404);
        }

        return new UserResource($user);
    }

    public function delete(string $id): JsonResponse
    {
        $status = $this->userService->delete($id);

        if (!$status) {
            return response()->json(['errors' => 'User not found'], 404);
        }

        return response()->json([
            'data' => 'OK',
        ]);
    }
}
