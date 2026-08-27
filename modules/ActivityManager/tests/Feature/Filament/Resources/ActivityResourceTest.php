<?php

declare(strict_types=1);

use AcMarche\ActivityManager\Enums\RolesEnum;
use AcMarche\ActivityManager\Filament\Resources\Activities\Pages\CreateActivity;
use AcMarche\ActivityManager\Filament\Resources\Activities\Pages\EditActivity;
use AcMarche\ActivityManager\Filament\Resources\Activities\Pages\ListActivities;
use AcMarche\ActivityManager\Filament\Resources\Activities\Pages\ViewActivity;
use AcMarche\ActivityManager\Models\Activity;
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

it('renders list, create, view and edit pages', function (): void {
    $activity = Activity::factory()->create();

    livewire(ListActivities::class)->assertOk();
    livewire(CreateActivity::class)->assertOk();
    livewire(ViewActivity::class, ['record' => $activity->id])->assertOk();
    livewire(EditActivity::class, ['record' => $activity->id])->assertOk();
});

it('lists activities', function (): void {
    $activities = Activity::factory(3)->create();

    livewire(ListActivities::class)
        ->loadTable()
        ->assertCanSeeTableRecords($activities);
});

it('creates an activity via the form', function (): void {
    livewire(CreateActivity::class)
        ->fillForm([
            'name' => 'Yoga',
            'description' => 'Cours de yoga doux',
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(Activity::class, [
        'name' => 'Yoga',
        'description' => 'Cours de yoga doux',
    ]);
});

it('updates an activity via the form', function (): void {
    $activity = Activity::factory()->create(['name' => 'Tricot']);

    livewire(EditActivity::class, ['record' => $activity->id])
        ->fillForm(['name' => 'Tricot Avancé'])
        ->call('save')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Activity::class, [
        'id' => $activity->id,
        'name' => 'Tricot Avancé',
    ]);
});

it('validates required fields', function (array $data, array $errors): void {
    livewire(CreateActivity::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasFormErrors($errors)
        ->assertNotNotified();
})->with([
    '`name` required' => [['name' => null], ['name' => 'required']],
    '`name` max 150' => [['name' => str_repeat('a', 151)], ['name' => 'max']],
]);

it('forbids a stranger from listing', function (): void {
    $this->actingAs(User::factory()->create());

    livewire(ListActivities::class)->assertForbidden();
});
