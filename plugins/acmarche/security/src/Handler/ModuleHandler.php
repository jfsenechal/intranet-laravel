<?php

namespace AcMarche\Security\Handler;

use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Repository\RoleRepository;
use AcMarche\Security\Repository\UserRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

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

    /**
     * Updates a user's roles for a specific module.
     */
    public static function syncUserRolesForModule(Module $module, User|Model $user, array $dataFromForm): void
    {
        $roleIdsToProcess = Role::where('module_id', $module->id)
            ->whereIn('name', $dataFromForm['roles'])
            ->pluck('id')
            ->all();

        // 1. Get the IDs of the roles selected in the form that *actually belong* to the current module.
        // This filters $newRoleIdsFromForm to only include roles valid for $module.
        $targetRoleIdsForThisModule = Role::where('module_id', $module->id)
            ->whereIn('id', $roleIdsToProcess)
            ->pluck('id')
            ->all();

        // 2. Get all current role IDs for the user that are *NOT* from the current module.
        // These need to be preserved.
        $roleIdsFromOtherModules = $user->roles()
            ->where(function ($query) use ($module) {
                $query->where('roles.module_id', '!=', $module->id)
                    ->orWhereNull('roles.module_id'); // In case some roles aren't module-specific
            })
            ->pluck('roles.id') // Use 'roles.id' to be explicit
            ->all();

        // 3. Combine the roles from other modules with the new target roles for *this* module.
        // This forms the complete list of role IDs the user should have.
        $allRoleIdsToSync = array_unique(array_merge($roleIdsFromOtherModules, $targetRoleIdsForThisModule));

        // 4. Sync the user's roles.
        // This will:
        // - Add any roles in $allRoleIdsToSync that the user doesn't currently have.
        // - Remove any roles the user currently has that are NOT in $allRoleIdsToSync.
        // Effectively, it sets the user's roles to exactly $allRoleIdsToSync.
        $user->roles()->sync($allRoleIdsToSync);
    }

    public static function revokeUser(Module|Model $module, Model|User $user): void
    {
        $roleIdsToDetach = $user->roles() // Accesses the roles currently assigned to the user
        ->where('module_id', $module->id) // Filters these roles to only those belonging to the given module
        // 'module_id' is a column on your 'roles' table
        ->pluck('roles.id') // Get only the IDs of these roles.
        // 'roles.id' is important to specify the 'id' column of the 'roles' table,
        // not the pivot table's 'id' or another 'id'.
        ->all();

        // 2. If there are roles to detach, detach them.
        //    The detach() method removes entries from the 'role_user' pivot table.
        if (!empty($roleIdsToDetach)) {
            $user->roles()->detach($roleIdsToDetach);
        }
    }
}
