<?php

declare(strict_types=1);

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
