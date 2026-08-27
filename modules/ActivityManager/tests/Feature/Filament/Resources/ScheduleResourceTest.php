<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\CreateSchedule;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\EditSchedule;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\ViewSchedule;
use AcMarche\ActivityManager\Models\Activity;
use AcMarche\ActivityManager\Models\Schedule;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('activity-manager-panel'));

    $this->admin = User::factory()->create(['is_administrator' => true]);

    $this->mdaAdmin = User::factory()->create();
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_MDA_ADMIN->value]);
    $this->mdaAdmin->roles()->attach($role);

    $this->actingAs($this->admin);
});

it('renders create, view and edit pages', function (): void {
    $schedule = Schedule::factory()->create();

    livewire(CreateSchedule::class)->assertOk();
    livewire(ViewSchedule::class, ['record' => $schedule->id])->assertOk();
    livewire(EditSchedule::class, ['record' => $schedule->id])->assertOk();
});

it('creates a schedule via the form', function (): void {
    $activity = Activity::factory()->create();

    livewire(CreateSchedule::class)
        ->fillForm([
            'name' => 'Yoga - Septembre 2026',
            'start_date' => '2026-09-01',
            'end_date' => '2026-12-31',
            'activity_id' => $activity->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Schedule::class, [
        'name' => 'Yoga - Septembre 2026',
        'activity_id' => $activity->id,
    ]);
});

it('updates a schedule via the form', function (): void {
    $schedule = Schedule::factory()->create(['name' => 'Old']);

    livewire(EditSchedule::class, ['record' => $schedule->id])
        ->fillForm(['name' => 'New'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Schedule::class, [
        'id' => $schedule->id,
        'name' => 'New',
    ]);
});

it('validates required fields', function (array $data, array $errors): void {
    livewire(CreateSchedule::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` required' => [['name' => null, 'start_date' => '2026-09-01'], ['name' => 'required']],
    '`start_date` required' => [['name' => 'X', 'start_date' => null], ['start_date' => 'required']],
    '`end_date` before start_date' => [
        ['name' => 'X', 'start_date' => '2026-09-01', 'end_date' => '2026-08-01'],
        ['end_date' => 'after_or_equal'],
    ],
]);

it('forbids a stranger from creating', function (): void {
    $this->actingAs(User::factory()->create());

    livewire(CreateSchedule::class)->assertForbidden();
});
