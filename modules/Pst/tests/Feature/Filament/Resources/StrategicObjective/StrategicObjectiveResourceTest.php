<?php

declare(strict_types=1);

use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Pst\Enums\RoleEnum;
use AcMarche\Pst\Filament\Resources\StrategicObjective\Pages\CreateStrategicObjective;
use AcMarche\Pst\Filament\Resources\StrategicObjective\Pages\EditStrategicObjective;
use AcMarche\Pst\Filament\Resources\StrategicObjective\Pages\ListStrategicObjectives;
use AcMarche\Pst\Filament\Resources\StrategicObjective\Pages\ViewStrategicObjective;
use AcMarche\Pst\Filament\Resources\StrategicObjective\RelationManagers\OosRelationManager;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('pst'));
    $adminRole = Role::factory()->create(['name' => RoleEnum::ADMIN->value]);
    $this->adminUser = User::factory()->create();
    $this->adminUser->roles()->attach($adminRole);

    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListStrategicObjectives::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateStrategicObjective::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = StrategicObjective::factory()->create();

        Livewire::test(ViewStrategicObjective::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = StrategicObjective::factory()->create();

        Livewire::test(EditStrategicObjective::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
                'position' => $record->position,
            ]);
    });
});

describe('list view', function (): void {
    it('displays record position and name', function (): void {
        $record = StrategicObjective::factory()->create([
            'scope' => ActionScopeEnum::EXTERNAL,
        ]);

        Livewire::test(ListStrategicObjectives::class)
            ->assertSeeHtml("{$record->position}. {$record->name}");
    });

    it('displays operational objectives count badge', function (): void {
        $record = StrategicObjective::factory()->create([
            'scope' => ActionScopeEnum::EXTERNAL,
        ]);
        OperationalObjective::factory(3)->create([
            'strategic_objective_id' => $record->id,
        ]);

        Livewire::test(ListStrategicObjectives::class)
            ->assertSeeHtml('3 Oos');
    });

    it('displays internal badge for internal records', function (): void {
        StrategicObjective::factory()->create([
            'scope' => ActionScopeEnum::INTERNAL,
        ]);

        Livewire::test(ListStrategicObjectives::class)
            ->assertSeeHtml('Interne');
    });

    it('does not display internal badge for external records', function (): void {
        StrategicObjective::factory()->create(['scope' => ActionScopeEnum::EXTERNAL]);

        Livewire::test(ListStrategicObjectives::class)
            ->assertDontSeeHtml('>Interne</span>');
    });
});

describe('crud operations', function (): void {
    it('can create a strategic objective', function (): void {
        $newData = StrategicObjective::factory()->make();

        Livewire::test(CreateStrategicObjective::class)
            ->fillForm([
                'name' => $newData->name,
                'scope' => $newData->scope,
                'position' => $newData->position,
            ])
            ->call('create')
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseHas(StrategicObjective::class, [
            'name' => $newData->name,
            'position' => $newData->position,
        ]);
    });

    it('can update a strategic objective', function (): void {
        $record = StrategicObjective::factory()->create();
        $newData = StrategicObjective::factory()->make();

        Livewire::test(EditStrategicObjective::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
                'position' => $newData->position,
                'scope' => $newData->scope,
            ])
            ->call('save')
            ->assertHasNoFormErrors()
            ->assertNotified();

        assertDatabaseHas(StrategicObjective::class, [
            'id' => $record->id,
            'name' => $newData->name,
            'position' => $newData->position,
        ]);
    });

    it('can delete a strategic objective', function (): void {
        $record = StrategicObjective::factory()->create();

        Livewire::test(ViewStrategicObjective::class, [
            'record' => $record->id,
        ])
            ->callAction(DeleteAction::class)
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseMissing($record);
    });

});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = StrategicObjective::factory()->make();

        Livewire::test(CreateStrategicObjective::class)
            ->fillForm([
                'name' => $newData->name,
                'position' => $newData->position,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
        '`position` is required' => [['position' => null], ['position' => 'required']],
        '`position` must be numeric' => [['position' => 'abc'], ['position' => 'numeric']],
    ]);

    it('validates the form data on edit', function (array $data, array $errors): void {
        $record = StrategicObjective::factory()->create();

        Livewire::test(EditStrategicObjective::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $record->name,
                'position' => $record->position,
                ...$data,
            ])
            ->call('save')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
        '`position` is required' => [['position' => null], ['position' => 'required']],
    ]);
});

describe('form fields', function (): void {
    it('has scope field', function (): void {
        Livewire::test(CreateStrategicObjective::class)
            ->assertFormFieldExists('scope');
    });
});

describe('relation manager', function (): void {
    it('can render the OosRelationManager', function (): void {
        $record = StrategicObjective::factory()->create();
        OperationalObjective::factory(3)->create([
            'strategic_objective_id' => $record->id,
        ]);

        Livewire::test(OosRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => ViewStrategicObjective::class,
        ])
            ->assertOk();
    });

    it('can list operational objectives in relation manager', function (): void {
        $record = StrategicObjective::factory()->create();
        $oos = OperationalObjective::factory(3)->create([
            'strategic_objective_id' => $record->id,
        ]);

        Livewire::test(OosRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => ViewStrategicObjective::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords($oos);
    });
});
