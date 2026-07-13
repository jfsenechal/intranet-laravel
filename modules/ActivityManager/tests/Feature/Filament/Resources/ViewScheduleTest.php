<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\EditSchedule;
use AcMarche\ActivityManager\Filament\Resources\Schedules\Pages\ViewSchedule;
use AcMarche\ActivityManager\Filament\Resources\Schedules\RelationManagers\MembersRelationManager;
use AcMarche\ActivityManager\Models\Member;
use AcMarche\ActivityManager\Models\Schedule;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('activity-manager-panel'));

    $this->admin = User::factory()->create(['is_administrator' => true]);

    $role = Role::factory()->create(['name' => RolesEnum::ROLE_MDA_ADMIN->value]);
    $this->mdaAdmin = User::factory()->create();
    $this->mdaAdmin->roles()->attach($role);

    $this->actingAs($this->admin);
});

it('attaches a member to the schedule via the header action', function (): void {
    $schedule = Schedule::factory()->create();
    $member = Member::factory()->create();

    livewire(ViewSchedule::class, ['record' => $schedule->id])
        ->callAction(TestAction::make('attachMember'), ['member_id' => $member->id])
        ->assertHasNoActionErrors();

    assertDatabaseHas('registrations', [
        'schedule_id' => $schedule->id,
        'member_id' => $member->id,
    ]);
});

it('requires a member for the attach action', function (): void {
    $schedule = Schedule::factory()->create();

    livewire(ViewSchedule::class, ['record' => $schedule->id])
        ->callAction(TestAction::make('attachMember'), ['member_id' => null])
        ->assertHasActionErrors(['member_id' => 'required']);
});

it('shows the members relation manager only on the view page', function (): void {
    $schedule = Schedule::factory()->create();

    expect(MembersRelationManager::canViewForRecord($schedule, ViewSchedule::class))->toBeTrue()
        ->and(MembersRelationManager::canViewForRecord($schedule, EditSchedule::class))->toBeFalse();
});

it('includes the members count in the relation manager title', function (): void {
    $schedule = Schedule::factory()->create();
    $schedule->members()->attach(Member::factory(3)->create());

    expect(MembersRelationManager::getTitle($schedule, ViewSchedule::class))->toBe('Inscrits (3)');
});
