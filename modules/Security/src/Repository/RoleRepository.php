<?php

declare(strict_types=1);

namespace AcMarche\Security\Repository;

use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;

final class RoleRepository
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
            ->whereHas('users', function ($query) use ($user): void { // Further filter: role must have the given user
                $query->where('users.id', $user->id); // Eloquent is smart enough to join 'role_user'
            })
            ->get();
    }

    public static function findRolesByModuleAndRolesName(Module $module, array $dataFromForm): array
    {
        return Role::where('module_id', $module->id)
            ->whereIn('name', $dataFromForm['roles'])
            ->pluck('id')
            ->all();
    }

    public static function findRolesByModuleAndRolesId(Module $module, array $roleIdsToProcess): array
    {
        return Role::where('module_id', $module->id)
            ->whereIn('id', $roleIdsToProcess)
            ->pluck('id')
            ->all();
    }

    // 2. Get all current role IDs for the user that are *NOT* from the current module.
    // These need to be preserved.
    public static function findRolesByUserAndNotModule(User $user, Module $module): array
    {
        return $user->roles()
            ->where(function ($query) use ($module): void {
                $query->where('roles.module_id', '!=', $module->id)
                    ->orWhereNull('roles.module_id'); // In case some roles aren't module-specific
            })
            ->pluck('roles.id') // Use 'roles.id' to be explicit
            ->all();
    }
}
