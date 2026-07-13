<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Filament\Resources\Activities\Pages\ViewActivity;
use AcMarche\ActivityManager\Filament\Resources\Activities\RelationManagers\SchedulesRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Schedules\SchedulesResource;
use AcMarche\ActivityManager\Models\Activity;
use AcMarche\ActivityManager\Models\Schedule;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('activity-manager-panel'));

    $this->admin = User::factory()->create(['is_administrator' => true]);

    $role = Role::factory()->create(['name' => RolesEnum::ROLE_MDA_ADMIN->value]);
    $this->mdaAdmin = User::factory()->create();
    $this->mdaAdmin->roles()->attach($role);

    $this->actingAs($this->admin);
});

it('links the schedule view action to the ViewSchedule page', function (): void {
    $activity = Activity::factory()->create();
    $schedule = Schedule::factory()->create(['activity_id' => $activity->id]);

    livewire(SchedulesRelationManager::class, [
        'ownerRecord' => $activity,
        'pageClass' => ViewActivity::class,
    ])
        ->assertActionHasUrl(
            TestAction::make(ViewAction::getDefaultName())->table($schedule),
            SchedulesResource::getUrl('view', ['record' => $schedule]),
        );
});
