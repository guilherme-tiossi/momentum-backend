<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function createUser(array $data)
    {
        $user = User::make($data['data']['attributes']);

        $user->save();

        return $user;
    }

    public function updateUser(array $data, User $user)
    {
        $user->fill($data['data']['attributes']);

        $user->save();

        return $user;
    }

    public function deleteUser(User $user)
    {
        $user->delete();
    }
}
