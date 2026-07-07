<?php

declare(strict_types=1);

use AcMarche\Security\Filament\Resources\Users\Pages\EditUser;
use AcMarche\Security\Filament\Resources\Users\RelationManagers\ModuleRelationManager;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin-panel'));
    auth()->user()->update(['is_administrator' => true]);
});

it('shows the module role description when editing an assigned module', function (): void {
    $user = User::factory()->create();
    $module = Module::factory()->create([
        'role_description' => 'Le rôle éditeur permet de publier des actualités.',
    ]);
    $user->modules()->attach($module);

    livewire(ModuleRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->loadTable()
        ->assertCanSeeTableRecords([$module])
        ->mountAction(TestAction::make('edit')->table($module))
        ->assertMountedActionModalSee('Le rôle éditeur permet de publier des actualités.');
});

it('lists only the roles the user owns for each module, not every module role', function (): void {
    $user = User::factory()->create();
    $module = Module::factory()->create();
    $ownedRole = Role::factory()->create(['name' => 'OWNEDROLE', 'module_id' => $module->id]);
    Role::factory()->create(['name' => 'OTHERROLE', 'module_id' => $module->id]);

    $user->modules()->attach($module);
    $user->roles()->attach($ownedRole);

    livewire(ModuleRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->loadTable()
        ->assertSee('OWNEDROLE')
        ->assertDontSee('OTHERROLE');
});

it('syncs the user roles for the module on edit, without writing to the modules table', function (): void {
    $user = User::factory()->create();
    $module = Module::factory()->create(['allow_multiple_roles' => true]);
    $roleA = Role::factory()->create(['name' => 'ROLEA', 'module_id' => $module->id]);
    Role::factory()->create(['name' => 'ROLEB', 'module_id' => $module->id]);

    $user->modules()->attach($module);
    $user->roles()->attach($roleA);

    livewire(ModuleRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->loadTable()
        ->callAction(TestAction::make('edit')->table($module), ['roles' => ['ROLEB']])
        ->assertHasNoActionErrors();

    $names = $user->roles()->where('module_id', $module->id)->pluck('name')->all();
    expect($names)->toContain('ROLEB')->not->toContain('ROLEA');
});

it('attaches a module and its selected role to the user on create', function (): void {
    $user = User::factory()->create();
    $module = Module::factory()->create(['is_public' => false]);
    Role::factory()->create(['name' => 'CREATEROLE', 'module_id' => $module->id]);

    livewire(ModuleRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->loadTable()
        ->callAction(TestAction::make('create')->table(), ['module' => $module->id, 'roles' => 'CREATEROLE'])
        ->assertHasNoActionErrors();

    expect($user->modules()->whereKey($module->id)->exists())->toBeTrue()
        ->and($user->roles()->where('module_id', $module->id)->pluck('name')->all())->toContain('CREATEROLE');
});

it('binds the row-click action to the existing edit action, not a missing view action', function (): void {
    $user = User::factory()->create();
    $module = Module::factory()->create();
    $user->modules()->attach($module);

    $table = livewire(ModuleRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->loadTable()
        ->instance()
        ->getTable();

    expect($table->getRecordAction($module))->toBe('edit');
});

it('hides the role description helper when the module has none', function (): void {
    $user = User::factory()->create();
    $module = Module::factory()->create(['role_description' => null]);
    $user->modules()->attach($module);

    livewire(ModuleRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->mountAction(TestAction::make('edit')->table($module))
        ->assertOk();
});
