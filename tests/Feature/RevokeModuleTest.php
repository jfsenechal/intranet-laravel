<?php

declare(strict_types=1);

use AcMarche\Security\Handler\ModuleHandler;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use App\Models\User;

it('detaches the module and only the roles of that module', function (): void {
    $user = User::factory()->create();

    $module = Module::factory()->create();
    $otherModule = Module::factory()->create();

    $role = Role::factory()->create(['module_id' => $module->id]);
    $otherRole = Role::factory()->create(['module_id' => $otherModule->id]);

    $user->modules()->attach([$module->id, $otherModule->id]);
    $user->roles()->attach([$role->id, $otherRole->id]);

    ModuleHandler::revokeModuleFromUser($user, $module->id);

    $user = $user->fresh();

    expect($user->modules->pluck('id')->all())->toBe([$otherModule->id])
        ->and($user->roles->pluck('id')->all())->toBe([$otherRole->id]);
});
