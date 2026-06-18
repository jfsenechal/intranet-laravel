<?php
declare(strict_types=1);

use AcMarche\Security\Filament\Resources\Users\Pages\EditUser;
use AcMarche\Security\Filament\Resources\Users\RelationManagers\ModuleRelationManager;
use AcMarche\Security\Models\Module;
use App\Models\User;
use Filament\Facades\Filament;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin-panel'));
    auth()->user()->update(['is_administrator' => true]);
});

it('reactive via fillForm', function (): void {
    $user = User::factory()->create();
    $module = Module::factory()->create([
        'is_public' => false,
        'role_description' => 'ROLE_DESC_MARKER',
    ]);

    livewire(ModuleRelationManager::class, [
        'ownerRecord' => $user,
        'pageClass' => EditUser::class,
    ])
        ->mountAction('create')
        ->setActionData(['module' => $module->id])
        ->assertSee('ROLE_DESC_MARKER');
});
