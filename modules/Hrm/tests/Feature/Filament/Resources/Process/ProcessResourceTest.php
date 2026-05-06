<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\Processes\Pages\CreateProcess;
use AcMarche\Hrm\Filament\Resources\Processes\Pages\EditProcess;
use AcMarche\Hrm\Filament\Resources\Processes\Pages\ListProcesses;
use AcMarche\Hrm\Models\Process;
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
        Livewire::test(ListProcesses::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateProcess::class)
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Process::factory()->create();

        Livewire::test(EditProcess::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can create a process', function (): void {
        $newData = Process::factory()->make();

        Livewire::test(CreateProcess::class)
            ->fillForm([
                'name' => $newData->name,
                'description' => $newData->description,
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Process::class, [
            'name' => $newData->name,
        ]);
    });

    it('can update a process', function (): void {
        $record = Process::factory()->create();
        $newData = Process::factory()->make();

        Livewire::test(EditProcess::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Process::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = Process::factory()->make();

        Livewire::test(CreateProcess::class)
            ->fillForm([
                'name' => $newData->name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 255 characters' => [['name' => Str::random(256)], ['name' => 'max']],
    ]);
});

describe('table', function (): void {
    it('can search by name', function (): void {
        $records = Process::factory(3)->create();
        $searchRecord = $records->first();

        Livewire::test(ListProcesses::class)
            ->loadTable()
            ->searchTable($searchRecord->name)
            ->assertCanSeeTableRecords($records->where('name', $searchRecord->name));
    });
});
