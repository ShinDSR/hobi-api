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

    public function getByID(?User $user = null, $hobby = null): HobbyResource|JsonResponse
    {
        $hobbyId = request()->route('hobby');
        $boundUser = request()->route('user');
        
        $targetUser = $this->getTargetUser($boundUser instanceof User ? $boundUser : null);
        $hobbyData = $this->hobbyService->getById($targetUser, $hobbyId);

        if (!$hobbyData) {
            return response()->json(['errors' => 'Hobby not found'], 404);
        }

        return new HobbyResource($hobbyData);
    }

    public function update(UpdateHobbyRequest $request, ?User $user = null, $hobby = null): HobbyResource|JsonResponse
    {
        $hobbyId = request()->route('hobby');
        $boundUser = request()->route('user');

        $targetUser = $this->getTargetUser($boundUser instanceof User ? $boundUser : null);
        $hobbyData = $this->hobbyService->update($targetUser, $hobbyId, $request->validated());

        if (!$hobbyData) {
            return response()->json(['errors' => 'Hobby not found'], 404);
        }

        return new HobbyResource($hobbyData);
    }

    public function delete(?User $user = null, $hobby = null): JsonResponse
    {
        $hobbyId = request()->route('hobby');
        $boundUser = request()->route('user');

        $targetUser = $this->getTargetUser($boundUser instanceof User ? $boundUser : null);
        $status = $this->hobbyService->delete($targetUser, $hobbyId);

        if (!$status) {
            return response()->json(['errors' => 'Hobby not found'], 404);
        }

        return response()->json([
            'data' => 'OK',
        ]);
    }
}
