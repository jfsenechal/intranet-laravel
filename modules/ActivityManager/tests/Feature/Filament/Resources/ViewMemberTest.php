<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Filament\Resources\Members\Pages\EditMember;
use AcMarche\ActivityManager\Filament\Resources\Members\Pages\ViewMember;
use AcMarche\ActivityManager\Filament\Resources\Members\RelationManagers\ActivitiesRelationManager;
use AcMarche\ActivityManager\Models\Activity;
use AcMarche\ActivityManager\Models\Member;
use AcMarche\ActivityManager\Models\Schedule;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
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

it('shows the activities relation manager only on the view page', function (): void {
    $member = Member::factory()->create();

    expect(ActivitiesRelationManager::canViewForRecord($member, ViewMember::class))->toBeTrue()
        ->and(ActivitiesRelationManager::canViewForRecord($member, EditMember::class))->toBeFalse();
});

it('attaches a schedule to the member via the header action', function (): void {
    $member = Member::factory()->create();
    $activity = Activity::factory()->create();
    $schedule = Schedule::factory()->create(['activity_id' => $activity->id]);

    livewire(ViewMember::class, ['record' => $member->id])
        ->callAction(TestAction::make('attachSchedule'), [
            'activity_id' => $activity->id,
            'schedule_id' => $schedule->id,
        ])
        ->assertHasNoActionErrors()
        ->assertNotified('Membre inscrit au cours')
        ->assertDispatched('member-schedules-updated');

    assertDatabaseHas('registrations', [
        'member_id' => $member->id,
        'schedule_id' => $schedule->id,
    ]);
});

it('requires both an activity and a schedule for the attach action', function (): void {
    $member = Member::factory()->create();

    livewire(ViewMember::class, ['record' => $member->id])
        ->callAction(TestAction::make('attachSchedule'), [
            'activity_id' => null,
            'schedule_id' => null,
        ])
        ->assertHasActionErrors([
            'activity_id' => 'required',
            'schedule_id' => 'required',
        ]);
});

it('rejects a schedule that does not belong to the selected activity', function (): void {
    $member = Member::factory()->create();
    $activity = Activity::factory()->create();
    $otherSchedule = Schedule::factory()->create();

    livewire(ViewMember::class, ['record' => $member->id])
        ->callAction(TestAction::make('attachSchedule'), [
            'activity_id' => $activity->id,
            'schedule_id' => $otherSchedule->id,
        ])
        ->assertHasActionErrors(['schedule_id']);
});

it('resets the selected schedule when the activity changes', function (): void {
    $member = Member::factory()->create();
    $schedule = Schedule::factory()->create();
    $otherActivity = Activity::factory()->create();

    livewire(ViewMember::class, ['record' => $member->id])
        ->mountAction(TestAction::make('attachSchedule'))
        ->setActionData([
            'activity_id' => $schedule->activity_id,
            'schedule_id' => $schedule->id,
        ])
        ->assertActionDataSet(['schedule_id' => $schedule->id])
        ->setActionData(['activity_id' => $otherActivity->id])
        ->assertActionDataSet(['schedule_id' => null]);
});

it('shows the attach and detach actions in the activities relation manager', function (): void {
    $member = Member::factory()->create();
    $schedule = Schedule::factory()->create();
    $member->schedules()->attach($schedule);

    livewire(ActivitiesRelationManager::class, [
        'ownerRecord' => $member,
        'pageClass' => ViewMember::class,
    ])
        ->loadTable()
        ->assertActionVisible(TestAction::make(AttachAction::getDefaultName())->table())
        ->assertActionVisible(TestAction::make(DetachAction::getDefaultName())->table($schedule));
});

it('refreshes the activities relation manager when a schedule is attached', function (): void {
    $member = Member::factory()->create();
    $schedule = Schedule::factory()->create();

    $component = livewire(ActivitiesRelationManager::class, [
        'ownerRecord' => $member,
        'pageClass' => ViewMember::class,
    ])
        ->loadTable()
        ->assertCanNotSeeTableRecords([$schedule]);

    $member->schedules()->attach($schedule);

    $component
        ->dispatch('member-schedules-updated')
        ->assertCanSeeTableRecords([$schedule]);
});
