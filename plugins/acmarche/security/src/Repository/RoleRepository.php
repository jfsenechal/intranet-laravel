<?php

namespace AcMarche\Security\Repository;

use AcMarche\Security\Models\Role;

class RoleRepository
{
    public static function findByNameAndModuleId(string $name, int $moduleId): ?Role
    {
        return Role::where('name', $name)
            ->where('module_id', $moduleId)
            ->first();
    }
}
