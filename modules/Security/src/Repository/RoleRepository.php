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
            ->where(function ($query) use ($module) {
                $query->where('roles.module_id', '!=', $module->id)
                    ->orWhereNull('roles.module_id'); // In case some roles aren't module-specific
            })
            ->pluck('roles.id') // Use 'roles.id' to be explicit
            ->all();
    }

    public static function findRolesByUserAndModule(User $user, Module $module): array
    {
        return $user->roles() // Accesses the roles currently assigned to the user
        ->where('module_id', $module->id) // Filters these roles to only those belonging to the given module
        // 'module_id' is a column on your 'roles' table
        ->pluck('roles.id') // Get only the IDs of these roles.
        // 'roles.id' is important to specify the 'id' column of the 'roles' table,
        // not the pivot table's 'id' or another 'id'.
        ->all();
    }
}
