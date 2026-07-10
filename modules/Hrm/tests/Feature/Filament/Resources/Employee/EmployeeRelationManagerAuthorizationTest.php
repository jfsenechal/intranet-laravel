<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Filament\Resources\Employees\Pages\ViewEmployee;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\ContractsRelationManager;
use AcMarche\Hrm\Filament\Resources\Employees\RelationManagers\DocumentsRelationManager;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\HrDocument;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('hrm-panel'));
});

function grhAdmin(): User
{
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_ADMIN->value]);
    $user = User::factory()->create(['is_administrator' => false, 'username' => 'grh-admin']);
    $user->roles()->attach($role);

    return $user;
}

function grhReader(string $username): User
{
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_GRH_VILLE_READ->value]);
    $user = User::factory()->create(['is_administrator' => false, 'username' => $username]);
    $user->roles()->attach($role);

    return $user;
}

describe('edit action visibility for a relation()-based relation manager', function (): void {
    it('shows the edit action to a ROLE_GRH_ADMIN user', function (): void {
        $this->actingAs(grhAdmin());
        $employee = Employee::factory()->create();
        $contract = Contract::factory()->create(['employee_id' => $employee->id]);

        livewire(ContractsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertActionVisible(TestAction::make('edit')->table($contract));
    });

    it('hides the edit action from a read-only hrm user', function (): void {
        $employee = Employee::factory()->create(['username' => 'jdoe']);
        $contract = Contract::factory()->create(['employee_id' => $employee->id]);
        $this->actingAs(grhReader('jdoe'));

        livewire(ContractsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertActionHidden(TestAction::make('edit')->table($contract));
    });
});

describe('edit action visibility for a configure()-based relation manager', function (): void {
    it('shows the edit action to a ROLE_GRH_ADMIN user', function (): void {
        $this->actingAs(grhAdmin());
        $employee = Employee::factory()->create();
        $document = HrDocument::factory()->create(['employee_id' => $employee->id]);

        livewire(DocumentsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertActionVisible(TestAction::make('edit')->table($document));
    });

    it('hides the edit action from a read-only hrm user', function (): void {
        $employee = Employee::factory()->create(['username' => 'jdoe']);
        $document = HrDocument::factory()->create(['employee_id' => $employee->id]);
        $this->actingAs(grhReader('jdoe'));

        livewire(DocumentsRelationManager::class, [
            'ownerRecord' => $employee,
            'pageClass' => ViewEmployee::class,
        ])
            ->assertActionHidden(TestAction::make('edit')->table($document));
    });
});
