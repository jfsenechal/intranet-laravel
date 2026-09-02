<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Security\Enums\RolesEnum;
use AcMarche\Security\Models\Role;
use AcMarche\WhoIsWho\Filament\Pages\Index;
use AcMarche\WhoIsWho\Filament\Resources\Employees\EmployeeResource;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('who-is-who-panel'));

    $this->actingAs(User::factory()->create(['is_administrator' => false]));
});

it('links each directory card to the agent view page', function (): void {
    $employee = Employee::factory()->create([
        'status' => StatusEnum::AGENT->value,
        'is_archived' => false,
    ]);

    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => null,
    ]);

    livewire(Index::class)
        ->assertOk()
        ->assertSee($employee->last_name)
        ->assertSeeHtml(EmployeeResource::getUrl('view', ['record' => $employee]));
});

describe('xlsx export action', function (): void {
    it('hides the export action from a user without the intranet admin role', function (): void {
        livewire(Index::class)
            ->assertActionHidden('export');
    });

    it('shows the export action to an intranet admin', function (): void {
        $admin = User::factory()->create(['is_administrator' => false]);
        $admin->roles()->attach(Role::factory()->create(['name' => RolesEnum::INTRANET_ADMIN->value]));

        $this->actingAs($admin);

        livewire(Index::class)
            ->assertActionVisible('export');
    });

    it('streams the directory when an intranet admin runs the export', function (): void {
        $admin = User::factory()->create(['is_administrator' => false]);
        $admin->roles()->attach(Role::factory()->create(['name' => RolesEnum::INTRANET_ADMIN->value]));

        $this->actingAs($admin);

        $employee = Employee::factory()->create([
            'status' => StatusEnum::AGENT->value,
            'is_archived' => false,
        ]);

        Contract::factory()->create([
            'employee_id' => $employee->id,
            'is_closed' => false,
            'is_suspended' => false,
            'end_date' => null,
        ]);

        livewire(Index::class)
            ->callAction('export')
            ->assertHasNoActionErrors()
            ->assertFileDownloaded('annuaire.xlsx');
    });
});
