<?php

declare(strict_types=1);

use AcMarche\Security\Handler\ModuleHandler;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use App\Models\User;

it('syncs a single role name coming from a radio field', function (): void {
    $user = User::factory()->create();

    $module = Module::factory()->create(['allow_multiple_roles' => false]);
    $role = Role::factory()->create(['module_id' => $module->id, 'name' => 'guichet_admin']);
    Role::factory()->create(['module_id' => $module->id, 'name' => 'guichet_user']);

    ModuleHandler::syncUserRolesForModule($module, $user, ['roles' => 'guichet_admin']);

    expect($user->fresh()->roles->pluck('id')->all())->toBe([$role->id]);
});

it('syncs several role names coming from a checkbox list', function (): void {
    $user = User::factory()->create();

    $module = Module::factory()->create(['allow_multiple_roles' => true]);
    $admin = Role::factory()->create(['module_id' => $module->id, 'name' => 'guichet_admin']);
    $reader = Role::factory()->create(['module_id' => $module->id, 'name' => 'guichet_user']);

    ModuleHandler::syncUserRolesForModule($module, $user, ['roles' => ['guichet_admin', 'guichet_user']]);

    expect($user->fresh()->roles->pluck('id')->all())->toBe([$admin->id, $reader->id]);
});

it('removes the roles of the module but keeps the others when nothing is selected', function (): void {
    $user = User::factory()->create();

    $module = Module::factory()->create();
    $otherModule = Module::factory()->create();

    $role = Role::factory()->create(['module_id' => $module->id, 'name' => 'guichet_admin']);
    $otherRole = Role::factory()->create(['module_id' => $otherModule->id, 'name' => 'other_admin']);

    $user->roles()->attach([$role->id, $otherRole->id]);

    ModuleHandler::syncUserRolesForModule($module, $user, []);

    expect($user->fresh()->roles->pluck('id')->all())->toBe([$otherRole->id]);
});
