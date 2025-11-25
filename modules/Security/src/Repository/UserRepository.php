<?php

namespace AcMarche\Security\Repository;

use App\Models\User;

class UserRepository
{
    public static function getUsersForSelect(): array
    {
        $users = [];
        foreach (User::all() as $user) {
            $users[$user->id] = $user->first_name.' '.$user->last_name;
        }

        return $users;
    }

    public static function find(int $userId): ?User
    {
        return User::find($userId);
    }
}
