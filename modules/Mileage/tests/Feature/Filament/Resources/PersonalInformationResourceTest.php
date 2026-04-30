<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Filament\Resources\PersonalInformation\Pages\ManagePersonalInformation;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('mileage-panel'));

    // Register dummy routes to prevent URL generation errors in tests
    if (! Route::getRoutes()->getByName('filament.mileage-panel.resources.personal-information.index')) {
        Route::get('/personal-information', fn (): string => '')->name('filament.mileage-panel.resources.personal-information.index');
    }

    $this->user = User::factory()->create(['username' => 'testuser', 'is_administrator' => true]);
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value]);
    $this->user->roles()->attach($role);
    PersonalInformation::factory()->create(['username' => $this->user->username]);
    $this->actingAs($this->user);
});

it('can render the personal information page', function (): void {
    livewire(ManagePersonalInformation::class)
        ->assertOk();
});

it('can display the table', function (): void {
    livewire(ManagePersonalInformation::class)
        ->loadTable()
        ->assertOk();
});

it('has table columns for personal information', function (string $column): void {
    livewire(ManagePersonalInformation::class)
        ->assertTableColumnExists($column);
})->with(['street', 'city', 'iban', 'car_license_plate1']);

it('displays edit and delete actions on table', function (): void {
    livewire(ManagePersonalInformation::class)
        ->assertTableActionExists('edit')
        ->assertTableActionExists('delete');
});
