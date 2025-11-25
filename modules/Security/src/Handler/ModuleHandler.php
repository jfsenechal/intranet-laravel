<?php

namespace AcMarche\Security\Handler;

use AcMarche\Security\Models\Module;
use AcMarche\Security\Repository\ModuleRepository;
use AcMarche\Security\Repository\RoleRepository;
use AcMarche\Security\Repository\UserRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ModuleHandler
{
    /**
     * @param Module $module
     * @param array $data
     * @return void
     * @throws \Exception
     */
    public static function addUserFromModule(Module $module, array $data): void
    {
        $userId = $data['user'];
        if (!$user = UserRepository::find($userId)) {
            throw new \Exception('User not found');
        }
        self::addModuleAndRoles($module, $user, $data);
    }

    public static function addModuleFromUser(User $user, array $data): void
    {
        if (!$module = ModuleRepository::find($data['module'])) {
            throw new \Exception('Module not found');
        }
        self::addModuleAndRoles($module, $user, $data);
    }

    private static function addModuleAndRoles(Module $module, User $user, array $data)
    {
        foreach ($data['roles'] as $roleName) {
            if ($role = RoleRepository::findByName($roleName)) {
                $user->addRole($role);
            }
        }
        $user->addModule($module);
    }

    /**
     * Updates a user's roles for a specific module.
     */
    public static function syncUserRolesForModule(Module $module, User|Model $user, array $dataFromForm): void
    {
        $roleIdsToProcess = RoleRepository::findRolesByModuleAndRolesName($module, $dataFromForm);

        // 1. Get the IDs of the roles selected in the form that *actually belong* to the current module.
        // This filters $newRoleIdsFromForm to only include roles valid for $module.
        $targetRoleIdsForThisModule = RoleRepository::findRolesByModuleAndRolesId($module, $roleIdsToProcess);

        // 2. Get all current role IDs for the user that are *NOT* from the current module.
        // These need to be preserved.
        $roleIdsFromOtherModules = RoleRepository::findRolesByUserAndNotModule($user, $module);

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
        $roleIdsToDetach = RoleRepository::findRolesByUserAndModule($user, $module);

        if (!empty($roleIdsToDetach)) {
            $user->roles()->detach($roleIdsToDetach);
        }

        $user->modules()->detach($module);
    }
}
