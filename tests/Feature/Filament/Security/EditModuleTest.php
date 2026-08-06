<?php

declare(strict_types=1);

use AcMarche\Security\Filament\Resources\Modules\Pages\EditModule;
use AcMarche\Security\Filament\Resources\Modules\Pages\ViewModule;
use AcMarche\Security\Filament\Resources\Modules\RelationManagers\RoleRelationManager;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('security-panel'));
    auth()->user()->update(['is_administrator' => true]);
});

it('does not render any relation manager on the edit module page', function (): void {
    $module = Module::factory()->create();
    Role::factory()->create(['name' => 'MODULEROLE', 'module_id' => $module->id]);

    $component = livewire(EditModule::class, ['record' => $module->id])
        ->assertSuccessful()
        ->assertDontSee('MODULEROLE');

    expect($component->instance()->getCachedRelationManagers())->toBe([]);
});

it('renders the module description line breaks as <br> tags, escaping the text', function (): void {
    $module = Module::factory()->create([
        'description' => "Première ligne\nSeconde <b>ligne</b>",
    ]);

    livewire(ViewModule::class, ['record' => $module->id])
        ->assertSuccessful()
        ->assertSee('Première ligne<br />'."\n".'Seconde &lt;b&gt;ligne&lt;/b&gt;', escape: false);
});

it('still declares the role relation manager on the resource for other pages', function (): void {
    expect(AcMarche\Security\Filament\Resources\Modules\ModuleResource::getRelations())
        ->toContain(RoleRelationManager::class);
});
