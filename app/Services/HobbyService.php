<?php

namespace App\Services;

use App\Models\Hobby;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class HobbyService
{
    public function getAll(User $user): LengthAwarePaginator
    {
        return $user->hobbies()->with('user')->paginate(10);
    }

    public function create(User $user, array $data): Hobby
    {
        return $user->hobbies()->create($data)->load('user');
    }

    public function getById(User $user, mixed $hobbyId): ?Hobby
    {
        return $user->hobbies()->with('user')->where('id', $hobbyId)->first();
    }

    public function update(User $user, mixed $hobbyId, array $data): ?Hobby
    {
        $hobby = $this->getById($user, $hobbyId);
        if (!$hobby) {
            return null;
        }
        $hobby->update($data);
        return $hobby->load('user');
    }

    public function delete(User $user, mixed $hobbyId): bool
    {
        $hobby = $this->getById($user, $hobbyId);
        if (!$hobby) {
            return false;
        }
        return $hobby->delete();
    }
}
