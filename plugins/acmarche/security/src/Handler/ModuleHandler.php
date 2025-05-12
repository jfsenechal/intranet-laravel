<?php

namespace AcMarche\Security\Handler;

use AcMarche\Security\Repository\RoleRepository;
use AcMarche\Security\Repository\UserRepository;

class ModuleHandler
{
    /**
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public static function addUser(array $data): void
    {
        $userId = $data['user'];
        if (!$user = UserRepository::find($userId)) {
            throw new \Exception('User not found');
        }
        foreach ($data['roles'] as $roleName) {
            if ($role = RoleRepository::findByName($roleName)) {
                $user->addRole($role);
            }
        }
    }
}
