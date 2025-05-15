<?php

namespace AcMarche\Security\Repository;

use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

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

    public static function findByModuleAndUser(Module $module, User $user): Collection
    {
        return Role::query()
            ->where('module_id', $module->id) // Filter roles by the given module
            ->whereHas('users', function ($query) use ($user) { // Further filter: role must have the given user
                $query->where('users.id', $user->id); // Eloquent is smart enough to join 'role_user'
            })
            ->get();
    }
}
