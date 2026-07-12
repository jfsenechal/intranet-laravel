<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Exports\DeadlineExport;
use AcMarche\Hrm\Filament\Resources\Deadlines\Pages\CreateDeadline;
use AcMarche\Hrm\Filament\Resources\Deadlines\Pages\EditDeadline;
use AcMarche\Hrm\Filament\Resources\Deadlines\Pages\ListDeadlines;
use AcMarche\Hrm\Filament\Resources\Deadlines\Pages\ViewDeadline;
use AcMarche\Hrm\Models\Deadline;
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
        Livewire::test(ListDeadlines::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateDeadline::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Deadline::factory()->create();

        Livewire::test(ViewDeadline::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Deadline::factory()->create();

        Livewire::test(EditDeadline::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'name' => $record->name,
            ]);
    });
});

describe('crud operations', function (): void {
    it('can create a deadline', function (): void {
        $newData = Deadline::factory()->make();

        Livewire::test(CreateDeadline::class)
            ->fillForm([
                'name' => $newData->name,
                'employee_id' => $newData->employee_id,
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Deadline::class, [
            'name' => $newData->name,
        ]);
    });

    it('can update a deadline', function (): void {
        $record = Deadline::factory()->create();
        $newData = Deadline::factory()->make();

        Livewire::test(EditDeadline::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'name' => $newData->name,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Deadline::class, [
            'id' => $record->id,
            'name' => $newData->name,
        ]);
    });

    it('accepts a deeply nested rich editor note without hitting the livewire nesting limit', function (): void {
        // The RichEditor syncs its value as a TipTap document tree. Deeply nested
        // lists produce a property path deeper than Livewire's default limit of 10,
        // which previously threw MaxNestingDepthExceededException on save.
        // See config/livewire.php -> payload.max_nesting_depth.
        $record = Deadline::factory()->create();

        // A property path of 12 segments, deeper than the default 10, matching the
        // shape produced by nested bullet lists (data.note.content...text).
        $deepPath = 'data.note.content.0.content.0.content.0.content.0.content.0.text';

        Livewire::test(EditDeadline::class, [
            'record' => $record->id,
        ])
            ->set($deepPath, 'Nested list item')
            ->assertOk();
    });

    it('can replicate a deadline from the view page', function (): void {
        $record = Deadline::factory()->create([
            'name' => 'Original Deadline',
            'is_closed' => true,
            'closed_date' => now(),
        ]);

        $targetEmployee = AcMarche\Hrm\Models\Employee::factory()->create();

        Livewire::test(ViewDeadline::class, [
            'record' => $record->id,
        ])
            ->callAction('replicate', ['employee_id' => $targetEmployee->id])
            ->assertHasNoActionErrors();

        expect(Deadline::query()->where('name', 'Original Deadline')->count())->toBe(2);

        $replica = Deadline::query()
            ->where('name', 'Original Deadline')
            ->where('id', '!=', $record->id)
            ->first();

        expect($replica->employee_id)->toBe($targetEmployee->id);
        expect($replica->is_closed)->toBeTrue();
        expect($replica->closed_date)->not->toBeNull();
    });
});

describe('form validation', function (): void {
    it('validates the form data on create', function (array $data, array $errors): void {
        $newData = Deadline::factory()->make();

        Livewire::test(CreateDeadline::class)
            ->fillForm([
                'name' => $newData->name,
                ...$data,
            ])
            ->call('create')
            ->assertHasFormErrors($errors)
            ->assertNotNotified();
    })->with([
        '`name` is required' => [['name' => null], ['name' => 'required']],
        '`name` is max 250 characters' => [['name' => Str::random(251)], ['name' => 'max']],
    ]);
});

describe('export action', function (): void {
    it('renders the export action on the index page', function (): void {
        Livewire::test(ListDeadlines::class)
            ->assertActionExists('export');
    });

    it('can trigger the export action with all columns', function (): void {
        Deadline::factory(2)->create();

        Livewire::test(ListDeadlines::class)
            ->callAction('export', data: ['columns' => array_keys(DeadlineExport::columns())])
            ->assertHasNoActionErrors();
    });

    it('can trigger the export action with a subset of columns', function (): void {
        Deadline::factory(2)->create();

        Livewire::test(ListDeadlines::class)
            ->callAction('export', data: ['columns' => ['name', 'agent', 'end_date']])
            ->assertHasNoActionErrors();
    });

    it('requires at least one column to be selected', function (): void {
        Livewire::test(ListDeadlines::class)
            ->callAction('export', data: ['columns' => []])
            ->assertHasActionErrors(['columns']);
    });
});
