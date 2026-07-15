<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Exports\SmsReminderExport;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\CreateSmsReminder;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\EditSmsReminder;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\ListSmsReminders;
use AcMarche\Hrm\Filament\Resources\SmsReminders\Pages\ViewSmsReminder;
use AcMarche\Hrm\Models\SmsReminder;
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
        Livewire::test(ListSmsReminders::class)
            ->assertOk();
    });

    it('can render the create page', function (): void {
        Livewire::test(CreateSmsReminder::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = SmsReminder::factory()->create();

        Livewire::test(ViewSmsReminder::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = SmsReminder::factory()->create();

        Livewire::test(EditSmsReminder::class, [
            'record' => $record->id,
        ])
            ->assertOk()
            ->assertSchemaStateSet([
                'phone_number' => $record->phone_number,
            ]);
    });

    it('can render the view page without an employee', function (): void {
        $record = SmsReminder::factory()->create(['employee_id' => null]);

        Livewire::test(ViewSmsReminder::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page without an employee', function (): void {
        $record = SmsReminder::factory()->create(['employee_id' => null]);

        Livewire::test(EditSmsReminder::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });
});

describe('crud operations', function (): void {
    it('can create a sms reminder', function (): void {
        $newData = SmsReminder::factory()->make();

        Livewire::test(CreateSmsReminder::class)
            ->fillForm([
                'phone_number' => $newData->phone_number,
                'message' => $newData->message,
                'reminder_date' => $newData->reminder_date->format('Y-m-d'),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(SmsReminder::class, [
            'phone_number' => $newData->phone_number,
        ]);
    });

    it('can create a sms reminder without an employee', function (): void {
        $newData = SmsReminder::factory()->make();

        Livewire::test(CreateSmsReminder::class)
            ->fillForm([
                'employee_id' => null,
                'phone_number' => $newData->phone_number,
                'message' => $newData->message,
                'reminder_date' => $newData->reminder_date->format('Y-m-d'),
            ])
            ->call('create')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(SmsReminder::class, [
            'phone_number' => $newData->phone_number,
            'employee_id' => null,
        ]);
    });

    it('can update a sms reminder', function (): void {
        $record = SmsReminder::factory()->create();

        Livewire::test(EditSmsReminder::class, [
            'record' => $record->id,
        ])
            ->fillForm([
                'message' => 'Updated message',
            ])
            ->call('save')
            ->assertNotified()
            ->assertHasNoFormErrors();

        assertDatabaseHas(SmsReminder::class, [
            'id' => $record->id,
            'message' => 'Updated message',
        ]);
    });
});

describe('form validation', function (): void {
    it('requires reminder_date on create', function (): void {
        Livewire::test(CreateSmsReminder::class)
            ->fillForm([
                'phone_number' => '32476123456',
                'message' => 'Test',
            ])
            ->call('create')
            ->assertHasFormErrors(['reminder_date' => 'required'])
            ->assertNotNotified();
    });

    it('requires message on create', function (): void {
        Livewire::test(CreateSmsReminder::class)
            ->fillForm([
                'phone_number' => '32476123456',
                'message' => null,
                'reminder_date' => '2026-08-05',
            ])
            ->call('create')
            ->assertHasFormErrors(['message' => 'required'])
            ->assertNotNotified();
    });
});

describe('reminder date filter', function (): void {
    it('filters records matching reminder_date within the range', function (): void {
        $inRange = SmsReminder::factory()->create(['reminder_date' => '2026-03-15']);
        $outOfRange = SmsReminder::factory()->create(['reminder_date' => '2026-05-20']);

        Livewire::test(ListSmsReminders::class)
            ->loadTable()
            ->filterTable('reminder_date', [
                'reminder_from' => '2026-03-01',
                'reminder_until' => '2026-03-31',
            ])
            ->assertCanSeeTableRecords([$inRange])
            ->assertCanNotSeeTableRecords([$outOfRange]);
    });

    it('filters records matching other_reminder_date within the range', function (): void {
        $inRange = SmsReminder::factory()->create([
            'reminder_date' => '2026-05-20',
            'other_reminder_date' => '2026-03-10',
        ]);
        $outOfRange = SmsReminder::factory()->create([
            'reminder_date' => '2026-05-20',
            'other_reminder_date' => '2026-06-10',
        ]);

        Livewire::test(ListSmsReminders::class)
            ->loadTable()
            ->filterTable('reminder_date', [
                'reminder_from' => '2026-03-01',
                'reminder_until' => '2026-03-31',
            ])
            ->assertCanSeeTableRecords([$inRange])
            ->assertCanNotSeeTableRecords([$outOfRange]);
    });
});

describe('export action', function (): void {
    it('renders the export action on the index page', function (): void {
        Livewire::test(ListSmsReminders::class)
            ->assertActionExists('export');
    });

    it('can trigger the export action with all columns', function (): void {
        SmsReminder::factory(2)->create();

        Livewire::test(ListSmsReminders::class)
            ->callAction('export', data: ['columns' => array_keys(SmsReminderExport::columns())])
            ->assertHasNoActionErrors();
    });

    it('can trigger the export action with a subset of columns', function (): void {
        SmsReminder::factory(2)->create();

        Livewire::test(ListSmsReminders::class)
            ->callAction('export', data: ['columns' => ['agent', 'phone_number', 'reminder_date']])
            ->assertHasNoActionErrors();
    });

    it('requires at least one column to be selected', function (): void {
        Livewire::test(ListSmsReminders::class)
            ->callAction('export', data: ['columns' => []])
            ->assertHasActionErrors(['columns']);
    });
});
