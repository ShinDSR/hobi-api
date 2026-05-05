<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserService
{
    public function getAll(): LengthAwarePaginator
    {
        return User::paginate(10);
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function getById(mixed $id): ?User
    {
        return User::find($id);
    }

    public function update(mixed $id, array $data): ?User
    {
        $user = User::find($id);
        if (!$user) {
            return null;
        }
        $user->update($data);
        return $user;
    }

    public function delete(mixed $id): bool
    {
        $user = User::find($id);
        if (!$user) {
            return false;
        }
        return $user->delete();
    }
}
