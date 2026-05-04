<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Resources\JobFunctions\Pages\CreateJobFunction;
use AcMarche\Hrm\Filament\Resources\JobFunctions\Pages\EditJobFunction;
use AcMarche\Hrm\Filament\Resources\JobFunctions\Pages\ListJobFunctions;
use AcMarche\Hrm\Filament\Resources\JobFunctions\Pages\ViewJobFunction;
use AcMarche\Hrm\Models\JobFunction;
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
        Livewire::test(ListJobFunctions::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateJobFunction::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = JobFunction::factory()->create();

        Livewire::test(ViewJobFunction::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = JobFunction::factory()->create();

        Livewire::test(EditJobFunction::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can create a job function', function (): void {
        $newData = JobFunction::factory()->make();

        Livewire::test(CreateJobFunction::class)
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(JobFunction::class, [
            'name' => $newData->name,
        ]);
    });

    it('can update a job function', function (): void {
        $record = JobFunction::factory()->create();
        $newData = JobFunction::factory()->make();

        Livewire::test(EditJobFunction::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(JobFunction::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = JobFunction::factory()->make();

        Livewire::test(CreateJobFunction::class)
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
    ]);
});

describe('table', function (): void {
    it('can search by name', function (): void {
        $records = JobFunction::factory(3)->create();
        $searchRecord = $records->first();

        Livewire::test(ListJobFunctions::class)
            ->loadTable()
            ->searchTable($searchRecord->name)
            ->assertCanSeeTableRecords($records->where('name', $searchRecord->name));
    });

    it('can sort by name', function (): void {
        $records = JobFunction::factory(3)->create();

        Livewire::test(ListJobFunctions::class)
            ->loadTable()
            ->sortTable('name')
            ->assertCanSeeTableRecords($records->sortBy('name'), inOrder: true);
    });
});
