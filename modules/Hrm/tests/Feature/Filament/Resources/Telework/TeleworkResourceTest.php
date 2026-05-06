<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\DayTypeEnum;
use AcMarche\Hrm\Enums\LocationTypeEnum;
use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\EditTelework;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\HrValidateTelework;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\ListTeleworks;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\ManagerValidateTelework;
use AcMarche\Hrm\Filament\Resources\Teleworks\Pages\ViewTelework;
use AcMarche\Hrm\Models\Telework;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
    $hrmAdminRole = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_ADMIN->value]);
    $this->adminUser = User::factory()->create(['is_administrator' => true]);
    $this->adminUser->roles()->attach($hrmAdminRole);
    $this->actingAs($this->adminUser);
});

describe('page rendering', function (): void {
    it('can render the index page', function (): void {
        Livewire::test(ListTeleworks::class)
            ->assertOk();
    });

    it('can render the view page', function (): void {
        $record = Telework::factory()->create();

        Livewire::test(ViewTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the edit page', function (): void {
        $record = Telework::factory()->create();

        Livewire::test(EditTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the manager validate page', function (): void {
        $record = Telework::factory()->create();

        Livewire::test(ManagerValidateTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });

    it('can render the hr validate page', function (): void {
        $record = Telework::factory()->create();

        Livewire::test(HrValidateTelework::class, [
            'record' => $record->id,
        ])
            ->assertOk();
    });
});

describe('model behavior', function (): void {
    it('automatically generates a uuid on creation', function (): void {
        $telework = Telework::factory()->create();

        expect($telework->uuid)
            ->not->toBeNull()
            ->toBeString();
    });

    it('casts location_type to LocationTypeEnum', function (): void {
        $telework = Telework::factory()->create([
            'location_type' => LocationTypeEnum::Domicile->value,
        ]);

        expect($telework->location_type)->toBe(LocationTypeEnum::Domicile);
    });

    it('casts day_type to DayTypeEnum', function (): void {
        $telework = Telework::factory()->create([
            'day_type' => DayTypeEnum::Fixe->value,
        ]);

        expect($telework->day_type)->toBe(DayTypeEnum::Fixe);
    });

    it('casts agreements as boolean', function (): void {
        $telework = Telework::factory()->create([
            'regulation_agreement' => true,
            'it_agreement' => true,
        ]);

        expect($telework->regulation_agreement)->toBeTrue();
        expect($telework->it_agreement)->toBeTrue();
    });
});
