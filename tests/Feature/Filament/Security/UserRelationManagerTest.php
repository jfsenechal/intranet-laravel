<?php

declare(strict_types=1);

use AcMarche\Security\Filament\Resources\Modules\Pages\ViewModule;
use AcMarche\Security\Filament\Resources\Modules\RelationManagers\UserRelationManager;
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

it('adds the selected user and role to the module using the user id', function (): void {
    $module = Module::factory()->create();
    $user = User::factory()->create();
    Role::factory()->create(['name' => 'ADDEDROLE', 'module_id' => $module->id]);

    livewire(UserRelationManager::class, [
        'ownerRecord' => $module,
        'pageClass' => ViewModule::class,
    ])
        ->loadTable()
        ->callAction(TestAction::make('create')->table(), ['user' => $user->id, 'roles' => 'ADDEDROLE'])
        ->assertHasNoActionErrors();

    expect($user->modules()->whereKey($module->id)->exists())->toBeTrue()
        ->and($user->roles()->where('module_id', $module->id)->pluck('name')->all())->toContain('ADDEDROLE');
});
