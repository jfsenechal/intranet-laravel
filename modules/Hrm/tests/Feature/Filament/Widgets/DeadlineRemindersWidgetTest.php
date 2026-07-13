<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Widgets\DeadlineRemindersWidget;
use AcMarche\Hrm\Models\Deadline;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $this->actingAs(User::factory()->create(['is_administrator' => true]));
});

it('shows deadlines with an upcoming reminder date', function (): void {
    $upcoming = Deadline::factory()->create([
        'reminder_date' => today()->addWeek(),
        'is_closed' => false,
    ]);

    livewire(DeadlineRemindersWidget::class)
        ->assertCanSeeTableRecords([$upcoming]);
});

it('hides deadlines without a reminder date, past reminders or closed ones', function (): void {
    $noReminder = Deadline::factory()->create(['reminder_date' => null]);
    $pastReminder = Deadline::factory()->create(['reminder_date' => today()->subDay()]);
    $closed = Deadline::factory()->create([
        'reminder_date' => today()->addWeek(),
        'is_closed' => true,
    ]);

    livewire(DeadlineRemindersWidget::class)
        ->assertCanNotSeeTableRecords([$noReminder, $pastReminder, $closed]);
});
