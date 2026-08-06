<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use AcMarche\WhoIsWho\Filament\Resources\Employees\EmployeeResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

use function Pest\Livewire\livewire;

/**
 * An agent celebrating a birthday today, opted in to being shown.
 */
function birthdayAgent(): Employee
{
    $employee = Employee::factory()->create([
        'status' => StatusEnum::AGENT->value,
        'is_archived' => false,
        'show_birthday' => true,
        'birth_date' => now()->subYears(40)->format('Y-m-d'),
    ]);

    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => null,
    ]);

    return $employee;
}

it('links a birthday to the who is who page for signed in users', function (): void {
    $employee = birthdayAgent();

    $this->actingAs(User::factory()->create());

    livewire('home.birthdays')
        ->assertOk()
        ->assertSee($employee->last_name)
        ->assertSeeHtml(EmployeeResource::getUrl(
            'view',
            ['record' => $employee],
            panel: 'who-is-who-panel',
        ));
});

/**
 * The homepage is public (the `/` route carries no auth middleware), so guests
 * reach this component too. They get the same link: the who-is-who panel enforces
 * its own auth and bounces them through login to the profile they asked for.
 */
it('links a birthday to the who is who page for guests as well', function (): void {
    $employee = birthdayAgent();

    // Tests\TestCase signs a default user in, so sign back out to act as a guest.
    Auth::logout();

    livewire('home.birthdays')
        ->assertOk()
        ->assertSee($employee->last_name)
        ->assertSeeHtml(EmployeeResource::getUrl(
            'view',
            ['record' => $employee],
            panel: 'who-is-who-panel',
        ));
});
