<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Prerequisites\Pages\CreatePrerequisite;
use AcMarche\Hrm\Filament\Resources\Prerequisites\Pages\EditPrerequisite;
use AcMarche\Hrm\Filament\Resources\Prerequisites\Pages\ListPrerequisites;
use AcMarche\Hrm\Filament\Resources\Prerequisites\Pages\ViewPrerequisite;
use AcMarche\Hrm\Models\Prerequisite;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListPrerequisites::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreatePrerequisite::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Prerequisite::factory()->create();

        Livewire::test(ViewPrerequisite::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Prerequisite::factory()->create();

        Livewire::test(EditPrerequisite::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can create a prerequisite', function (): void {
        $newData = Prerequisite::factory()->make();

        Livewire::test(CreatePrerequisite::class)
            ->fillForm([
                'name' => $newData->name,
                'profession' => $newData->profession,
                'user' => $newData->user,
                'description' => $newData->description,
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Prerequisite::class, [
            'name' => $newData->name,
        ]);
    });

    it('can update a prerequisite', function (): void {
        $record = Prerequisite::factory()->create();
        $newData = Prerequisite::factory()->make();

        Livewire::test(EditPrerequisite::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Prerequisite::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = Prerequisite::factory()->make();

        Livewire::test(CreatePrerequisite::class)
            ->fillForm([
                'name' => $newData->name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 150 characters' => [['name' => Str::random(151)], ['name' => 'max']],
        '`profession` is max 150 characters' => [['profession' => Str::random(151)], ['profession' => 'max']],
    ]);
});

describe('table', function (): void {
    it('can search by name', function (): void {
        $records = Prerequisite::factory(3)->create();
        $searchRecord = $records->first();

        Livewire::test(ListPrerequisites::class)
            ->loadTable()
            ->searchTable($searchRecord->name)
            ->assertCanSeeTableRecords($records->where('name', $searchRecord->name));
    });
});
