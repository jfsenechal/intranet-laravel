<?php

declare(strict_types=1);

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
});
