<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Filament\Resources\Users\Pages\CreateUser;
use AcMarche\Mileage\Filament\Resources\Users\Pages\EditUser;
use AcMarche\Mileage\Filament\Resources\Users\Pages\ListUsers;
use AcMarche\Mileage\Filament\Resources\Users\UserResource;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Providers\MileageServiceProvider;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('mileage-panel'));

    // Register dummy routes to prevent URL generation errors in tests
    if (! Route::getRoutes()->getByName('filament.mileage-panel.resources.users.index')) {
        Route::get('/users', fn (): string => '')->name('filament.mileage-panel.resources.users.index');
        Route::get('/users/create', fn (): string => '')->name('filament.mileage-panel.resources.users.create');
        Route::get('/users/{record}/edit', fn (): string => '')->name('filament.mileage-panel.resources.users.edit');
    }

    $this->user = User::factory()->create(['is_administrator' => true]);
    $this->adminRole = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
    $this->user->roles()->attach($this->adminRole);
    PersonalInformation::factory()->create(['username' => $this->user->username]);
    $this->actingAs($this->user);
});

it('can render the users index page', function (): void {
    livewire(ListUsers::class)
        ->assertOk();
});

it('can render the create user page', function (): void {
    livewire(CreateUser::class)
        ->assertOk();
});

it('can load the table', function (): void {
    livewire(ListUsers::class)
        ->loadTable()
        ->assertOk();
});

it('has table columns', function (string $column): void {
    livewire(ListUsers::class)
        ->assertTableColumnExists($column);
})->with(['email', 'last_name', 'first_name', 'departments']);

it('can load the create form with components', function (): void {
    livewire(CreateUser::class)
        ->assertSchemaComponentExists('username')
        ->assertSchemaComponentExists('college_trip_date')
        ->assertSchemaComponentExists('omnium');
});

it('displays edit action on list page', function (): void {
    livewire(ListUsers::class)
        ->assertTableActionExists('edit');
});

it('pre-checks the agent existing roles on the edit page', function (): void {
    $module = Module::factory()->create([
        'id' => MileageServiceProvider::$module_id,
        'allow_multiple_roles' => true,
    ]);
    $role = Role::factory()->create([
        'name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value,
        'module_id' => $module->id,
    ]);

    $agent = User::factory()->create();
    PersonalInformation::factory()->create(['username' => $agent->username]);
    $agent->roles()->attach($role);

    livewire(EditUser::class, ['record' => $agent->id])
        ->assertSchemaStateSet([
            'roles' => [$role->name],
        ]);
});

describe('access is reserved to the mileage administrators', function (): void {
    beforeEach(function (): void {
        $this->agent = User::factory()->create();
        PersonalInformation::factory()->create(['username' => $this->agent->username]);
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]);
        $this->agent->roles()->attach($role);
        $this->actingAs($this->agent);
    });

    it('forbids listing the agents', function (): void {
        livewire(ListUsers::class)->assertForbidden();
    });

    it('forbids creating an agent', function (): void {
        livewire(CreateUser::class)->assertForbidden();
    });

    it('forbids editing an agent', function (): void {
        livewire(EditUser::class, ['record' => $this->agent->id])->assertForbidden();
    });

    it('hides the resource from the navigation', function (): void {
        expect(UserResource::canAccess())->toBeFalse();
    });
});

it('grants access to a mileage admin who is not a global administrator', function (): void {
    $admin = User::factory()->create(['is_administrator' => false]);
    PersonalInformation::factory()->create(['username' => $admin->username]);
    $admin->roles()->attach($this->adminRole);
    $this->actingAs($admin);

    livewire(ListUsers::class)->assertOk();
});
