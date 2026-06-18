<?php

declare(strict_types=1);

use AcMarche\Security\Filament\Resources\Users\Pages\EditUser;
use AcMarche\Security\Filament\Resources\Users\RelationManagers\ModuleRelationManager;
use AcMarche\Security\Models\Module;
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
        ->assertSee('Le rôle éditeur permet de publier des actualités.');
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
