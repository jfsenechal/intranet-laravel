<?php

namespace AcMarche\Security\Repository;

use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;

class RoleRepository
{
    public static function findByNameAndModuleId(string $name, int $moduleId): ?Role
    {
        return Role::where('name', $name)
            ->where('module_id', $moduleId)
            ->first();
    }

    public static function getForSelect(Module $module): array
    {
        $rolesName = $rolesDescription = [];
        foreach ($module->roles as $role) {
            $rolesName[$role->name] = $role->name;
            $rolesDescription[$role->name] = $role->description;
        }

        return [$rolesName, $rolesDescription];
    }

    public static function findByName(string $roleName): ?Role
    {
        return Role::where('name', $roleName)->first();
    }
}
