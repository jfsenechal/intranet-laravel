<?php

declare(strict_types=1);

namespace AcMarche\Security\Handler;

use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Repository\ModuleRepository;
use AcMarche\Security\Repository\RoleRepository;
use AcMarche\Security\Repository\UserRepository;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;

final class ModuleHandler
{
    /**
     * @throws Exception
     */
    public static function addUserFromModule(Module $module, array $data): void
    {
        $userId = $data['user'];
        if (! ($user = UserRepository::find($userId)) instanceof User) {
            throw new Exception('User not found');
        }
        self::addModuleAndRoles($module, $user, $data);
    }

    public static function addModuleFromUser(User $user, int $moduleId, array $rolesChecked): void
    {
        if (! ($module = ModuleRepository::find($moduleId)) instanceof Module) {
            throw new Exception('Module not found');
        }
        self::addModuleAndRoles($module, $user, $rolesChecked);
    }

    /**
     * Updates a user's roles for a specific module.
     */
    public static function syncUserRolesForModule(Module $module, User|Model $user, array $rolesChecked): void
    {
        $roleIdsToProcess = RoleRepository::findRolesByModuleAndRolesName($module, $rolesChecked);

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

    public static function revokeModuleFromUser(Model|User $user, int $moduleId): void
    {
        $user->roles()
            ->where('module_id', $moduleId)
            ->detach();
    }

    private static function addModuleAndRoles(Module $module, User $user, array $data): void
    {
        foreach ($data['roles'] as $roleName) {
            if (($role = RoleRepository::findByName($roleName)) instanceof Role) {
                $user->addRole($role);
            }
        }
        $user->addModule($module);
    }
}
