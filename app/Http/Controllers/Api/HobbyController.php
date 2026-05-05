<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hobby\CreateHobbyRequest;
use App\Http\Requests\Hobby\UpdateHobbyRequest;
use App\Http\Resources\HobbyResource;
use App\Models\Hobby;
use App\Models\User;
use App\Services\HobbyService;
use Illuminate\Http\JsonResponse;

class HobbyController extends Controller
{
    protected HobbyService $hobbyService;

    public function __construct(HobbyService $hobbyService)
    {
        $this->hobbyService = $hobbyService;
    }

    private function getTargetUser(?User $user): User
    {
        if ($user && auth()->user()->is_admin) {
            return $user;
        }
        return auth()->user();
    }

    public function list(?User $user = null): JsonResponse
    {
        $targetUser = $this->getTargetUser($user);
        $hobbies = $this->hobbyService->getAll($targetUser);
        
        return response()->json([
            'data' => HobbyResource::collection($hobbies),
            'meta' => [
                'current_page' => $hobbies->currentPage(),
                'last_page' => $hobbies->lastPage(),
                'per_page' => $hobbies->perPage(),
                'total' => $hobbies->total(),
            ],
        ]);
    }

    public function create(CreateHobbyRequest $request, ?User $user = null): HobbyResource
    {
        $targetUser = $this->getTargetUser($user);
        $hobby = $this->hobbyService->create($targetUser, $request->validated());
        return new HobbyResource($hobby);
    }

    public function getByID(string $hobby, ?User $user = null): HobbyResource|JsonResponse
    {
        $targetUser = $this->getTargetUser($user);
        $hobbyData = $this->hobbyService->getById($targetUser, $hobby);

        if (!$hobbyData) {
            return response()->json(['errors' => 'Hobby not found'], 404);
        }

        return new HobbyResource($hobbyData);
    }

    public function update(UpdateHobbyRequest $request, string $hobby, ?User $user = null): HobbyResource|JsonResponse
    {
        $targetUser = $this->getTargetUser($user);
        $hobbyData = $this->hobbyService->update($targetUser, $hobby, $request->validated());

        if (!$hobbyData) {
            return response()->json(['errors' => 'Hobby not found'], 404);
        }

        return new HobbyResource($hobbyData);
    }

    public function delete(string $hobby, ?User $user = null): JsonResponse
    {
        $targetUser = $this->getTargetUser($user);
        $status = $this->hobbyService->delete($targetUser, $hobby);

        if (!$status) {
            return response()->json(['errors' => 'Hobby not found'], 404);
        }

        return response()->json([
            'data' => 'OK',
        ]);
    }
}
