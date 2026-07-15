<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Filament\Resources\Members\Pages\EditMember;
use AcMarche\ActivityManager\Filament\Resources\Members\Pages\ViewMember;
use AcMarche\ActivityManager\Filament\Resources\Members\RelationManagers\ActivitiesRelationManager;
use AcMarche\ActivityManager\Models\Member;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

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
