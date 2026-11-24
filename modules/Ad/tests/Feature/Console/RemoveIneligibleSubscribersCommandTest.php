<?php

declare(strict_types=1);

use AcMarche\Ad\Models\Subscriber;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

function createAgent(array $attributes, array $contract = []): Employee
{
    $employee = Employee::factory()->create([
        'status' => StatusEnum::AGENT->value,
        ...$attributes,
    ]);
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => now()->addMonth(),
        ...$contract,
    ]);

    return $employee;
}

it('keeps subscribers matching an eligible agent by professional or private email', function (): void {
    createAgent(['professional_email' => 'Alice@marche.be', 'private_email' => 'alice@example.com']);
    Subscriber::factory()->create(['email' => 'alice@marche.be']);
    Subscriber::factory()->create(['email' => 'alice@example.com']);

    $this->artisan('ad:remove-ineligible-subscribers')
        ->expectsOutputToContain('Removed 0 ineligible subscriber(s).')
        ->assertSuccessful();

    expect(Subscriber::query()->count())->toBe(2);
});

it('removes subscribers who are no longer eligible', function (): void {
    createAgent(['professional_email' => 'ended@marche.be'], ['is_closed' => true, 'end_date' => now()->subDay()]);
    createAgent(['professional_email' => 'student@marche.be', 'status' => StatusEnum::STUDENT->value]);
    createAgent(['professional_email' => 'still@marche.be']);

    Subscriber::factory()->create(['email' => 'ended@marche.be']);
    Subscriber::factory()->create(['email' => 'student@marche.be']);
    Subscriber::factory()->create(['email' => 'gone@marche.be']);
    Subscriber::factory()->create(['email' => 'still@marche.be']);

    $this->artisan('ad:remove-ineligible-subscribers')
        ->expectsOutputToContain('Removed 3 ineligible subscriber(s).')
        ->assertSuccessful();

    assertDatabaseMissing(Subscriber::class, ['email' => 'ended@marche.be']);
    assertDatabaseMissing(Subscriber::class, ['email' => 'student@marche.be']);
    assertDatabaseMissing(Subscriber::class, ['email' => 'gone@marche.be']);
    assertDatabaseHas(Subscriber::class, ['email' => 'still@marche.be']);
});
