<?php

declare(strict_types=1);

use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Filament\Resources\Odd\Pages\CreateOdd;
use AcMarche\Pst\Filament\Resources\Odd\Pages\EditOdd;
use AcMarche\Pst\Filament\Resources\Odd\Pages\ListOdds;
use AcMarche\Pst\Filament\Resources\Odd\Pages\ViewOdd;
use AcMarche\Pst\Filament\Resources\Odd\RelationManagers\ActionsRelationManager;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\Odd;
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
    $adminRole = Role::factory()->create(['name' => RolesEnum::ADMIN->value]);
    $this->adminUser = User::factory()->create();
    $this->adminUser->roles()->attach($adminRole);

    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListOdds::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateOdd::class)
            ->assertForbidden();
    });

    it('can render the view page', function (): void {
        $record = Odd::factory()->create();

        Livewire::test(ViewOdd::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Odd::factory()->create();

        Livewire::test(EditOdd::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can create an odd', function (): void {
        Livewire::test(CreateOdd::class)
            ->assertForbidden();
    });

    it('can create an odd with color', function (): void {
        Livewire::test(CreateOdd::class)
            ->assertForbidden();
    });

    it('can update an odd', function (): void {
        $record = Odd::factory()->create(['position' => 1]);
        $newData = Odd::factory()->make();

        Livewire::test(EditOdd::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified();

        assertDatabaseHas(Odd::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });

    it('cannot delete an odd', function (): void {
        $record = Odd::factory()->create(['position' => 1]);

        Livewire::test(EditOdd::class, [
            'record' => $record->id,
        ])
            ->assertActionDoesNotExist(DeleteAction::class);

        assertDatabaseHas(Odd::class, [
            'id' => $record->id,
        ]);
    });

    it('can delete an odd', function (): void {
        $record = Odd::factory()->create(['position' => 1]);

        Livewire::test(ViewOdd::class, [
            'record' => $record->id,
        ])
            ->callAction(DeleteAction::class)
            ->assertNotified()
            ->assertRedirect();

        assertDatabaseMissing($record);
    });
});

describe('form validation', function (): void {
    it('validates the form data on edit', function (array $data, array $errors): void {
        $record = Odd::factory()->create(['position' => 1]);

        Livewire::test(EditOdd::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $record->name,
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

describe('relation manager', function (): void {
    it('can render the ActionsRelationManager', function (): void {
        $record = Odd::factory()->create(['position' => 1]);
        $strategicObjective = StrategicObjective::factory()->create();
        $operationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ]);
        $actions = Action::factory(3)->create([
            'operational_objective_id' => $operationalObjective->id,
        ]);
        $record->actions()->attach($actions->pluck('id'));

        Livewire::test(ActionsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => ViewOdd::class,
        ])
            ->assertOk();
    });

    it('can list actions in relation manager', function (): void {
        $record = Odd::factory()->create(['position' => 1]);
        $strategicObjective = StrategicObjective::factory()->create();
        $operationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ]);
        $actions = Action::factory(3)->create([
            'operational_objective_id' => $operationalObjective->id,
        ]);
        $record->actions()->attach($actions->pluck('id'));

        Livewire::test(ActionsRelationManager::class, [
            'ownerRecord' => $record,
            'pageClass' => ViewOdd::class,
        ])
            ->loadTable()
            ->assertCanSeeTableRecords($actions);
    });
});
