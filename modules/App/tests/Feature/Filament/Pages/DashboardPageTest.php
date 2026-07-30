<?php

declare(strict_types=1);

use AcMarche\App\Filament\Pages\DashboardPage;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\WhoIsWho\Filament\Pages\Index as WhoIsWhoIndex;
use AcMarche\WhoIsWho\Filament\Resources\Employees\EmployeeResource;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('app-panel'));

    $this->user = User::factory()->create(['is_administrator' => false]);
    $this->actingAs($this->user);
});

function dashboardAgent(array $attributes = []): Employee
{
    $employee = Employee::factory()->create([
        'status' => StatusEnum::AGENT->value,
        'is_archived' => false,
        ...$attributes,
    ]);

    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => null,
    ]);

    return $employee;
}

it('lists the favorite agents of the user', function (): void {
    $favorite = dashboardAgent([
        'last_name' => 'Delvaux',
        'professional_phone' => '084 12 34 56',
        'professional_phone_extension' => '123',
    ]);
    dashboardAgent(['last_name' => 'Lambert']);

    $this->user->toggleFavoriteEmployee($favorite->id);

    Livewire::test(DashboardPage::class)
        ->assertOk()
        ->assertSee('Mes complices du quotidien')
        ->assertSee('Delvaux')
        ->assertSee('084 12 34 56 (ext. 123)')
        ->assertDontSee('Lambert')
        ->assertSeeHtml(EmployeeResource::getUrl('view', ['record' => $favorite], panel: 'who-is-who-panel'));
});

it('links to the directory to add a favorite agent', function (): void {
    $favorite = dashboardAgent(['last_name' => 'Delvaux']);

    $this->user->toggleFavoriteEmployee($favorite->id);

    Livewire::test(DashboardPage::class)
        ->assertOk()
        ->assertSee('Ajouter')
        ->assertSeeHtml(WhoIsWhoIndex::getUrl(panel: 'who-is-who-panel'));
});

it('invites the user to pick favorite agents when there is none', function (): void {
    Livewire::test(DashboardPage::class)
        ->assertOk()
        ->assertSee('Aucun collègue en favori', escape: false)
        ->assertSeeHtml(WhoIsWhoIndex::getUrl(panel: 'who-is-who-panel'));
});
