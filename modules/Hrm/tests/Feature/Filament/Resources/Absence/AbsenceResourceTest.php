<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\ReasonsEnum;
use AcMarche\Hrm\Filament\Exports\AbsenceExport;
use AcMarche\Hrm\Filament\Resources\Absences\Pages\CreateAbsence;
use AcMarche\Hrm\Filament\Resources\Absences\Pages\EditAbsence;
use AcMarche\Hrm\Filament\Resources\Absences\Pages\ListAbsences;
use AcMarche\Hrm\Filament\Resources\Absences\Pages\ViewAbsence;
use AcMarche\Hrm\Models\Absence;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListAbsences::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateAbsence::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Absence::factory()->create();

        Livewire::test(ViewAbsence::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Absence::factory()->create();

        Livewire::test(EditAbsence::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });
});

describe('crud operations', function (): void {
    it('can update an absence', function (): void {
        $record = Absence::factory()->create();

        Livewire::test(EditAbsence::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'reason' => ReasonsEnum::SICKNESS->value,
                'is_closed' => true,
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(Absence::class, [
            'id' => $record->id,
            'reason' => ReasonsEnum::SICKNESS->value,
            'is_closed' => 1,
        ]);
    });
});

describe('model behavior', function (): void {
    it('casts reason to ReasonsEnum', function (): void {
        $absence = Absence::factory()->create(['reason' => ReasonsEnum::SICKNESS->value]);

        expect($absence->reason)->toBe(ReasonsEnum::SICKNESS);
    });

    it('casts is_closed as boolean', function (): void {
        $absence = Absence::factory()->create(['is_closed' => true]);

        expect($absence->is_closed)->toBeTrue();
    });
});

describe('export action', function (): void {
    it('renders the export action on the index page', function (): void {
        Livewire::test(ListAbsences::class)
            ->assertActionExists('export');
    });

    it('can trigger the export action with all columns', function (): void {
        Absence::factory(2)->create();

        Livewire::test(ListAbsences::class)
            ->callAction('export', data: ['columns' => array_keys(AbsenceExport::columns())])
            ->assertHasNoActionErrors();
    });

    it('can trigger the export action with a subset of columns', function (): void {
        Absence::factory(2)->create();

        Livewire::test(ListAbsences::class)
            ->callAction('export', data: ['columns' => ['agent', 'start_date', 'end_date']])
            ->assertHasNoActionErrors();
    });

    it('requires at least one column to be selected', function (): void {
        Livewire::test(ListAbsences::class)
            ->callAction('export', data: ['columns' => []])
            ->assertHasActionErrors(['columns']);
    });
});
