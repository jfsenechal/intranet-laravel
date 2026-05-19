<?php

declare(strict_types=1);

use AcMarche\Ad\Models\Subscriber;
use AcMarche\Ad\Services\SubscriptionException;
use AcMarche\Ad\Services\SubscriptionService;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function (): void {
    $this->service = app(SubscriptionService::class);
});

it('subscribes an agent matched by professional email', function (): void {
    $employee = Employee::factory()->create([
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'professional_email' => 'alice.martin@marche.be',
        'private_email' => 'alice@example.com',
        'status' => StatusEnum::AGENT->value,
    ]);
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => now()->addMonth(),
    ]);

    $subscriber = $this->service->subscribe('alice.martin@marche.be');

    expect($subscriber)
        ->first_name->toBe('Alice')
        ->last_name->toBe('Martin')
        ->email->toBe('alice.martin@marche.be');

    assertDatabaseHas(Subscriber::class, [
        'email' => 'alice.martin@marche.be',
        'first_name' => 'Alice',
        'last_name' => 'Martin',
    ]);
});

it('subscribes an agent matched by private email', function (): void {
    $employee = Employee::factory()->create([
        'first_name' => 'Bob',
        'last_name' => 'Dupont',
        'professional_email' => null,
        'private_email' => 'bob.dupont@example.com',
        'status' => StatusEnum::AGENT->value,
    ]);
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => null,
    ]);

    $this->service->subscribe('bob.dupont@example.com');

    assertDatabaseHas(Subscriber::class, [
        'email' => 'bob.dupont@example.com',
        'first_name' => 'Bob',
        'last_name' => 'Dupont',
    ]);
});

it('rejects an email not present in HRM', function (): void {
    expect(fn () => $this->service->subscribe('unknown@example.com'))
        ->toThrow(SubscriptionException::class);

    assertDatabaseMissing(Subscriber::class, ['email' => 'unknown@example.com']);
});

it('rejects an employee whose status is not Agent', function (): void {
    $employee = Employee::factory()->create([
        'professional_email' => 'student@marche.be',
        'status' => StatusEnum::STUDENT->value,
    ]);
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => now()->addMonth(),
    ]);

    expect(fn () => $this->service->subscribe('student@marche.be'))
        ->toThrow(SubscriptionException::class);
});

it('rejects an agent without an active contract', function (): void {
    $employee = Employee::factory()->create([
        'professional_email' => 'inactive@marche.be',
        'status' => StatusEnum::AGENT->value,
    ]);
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => true,
        'end_date' => now()->subDay(),
    ]);

    expect(fn () => $this->service->subscribe('inactive@marche.be'))
        ->toThrow(SubscriptionException::class);
});

it('does not duplicate subscriptions for the same email', function (): void {
    $employee = Employee::factory()->create([
        'professional_email' => 'carla@marche.be',
        'first_name' => 'Carla',
        'last_name' => 'Noir',
        'status' => StatusEnum::AGENT->value,
    ]);
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
    ]);

    $this->service->subscribe('carla@marche.be');
    $this->service->subscribe('CARLA@marche.be');

    expect(Subscriber::query()->count())->toBe(1);
});

it('unsubscribes an existing subscriber', function (): void {
    Subscriber::factory()->create(['email' => 'someone@example.com']);

    $removed = $this->service->unsubscribe('someone@example.com');

    expect($removed)->toBeTrue();
    assertDatabaseMissing(Subscriber::class, ['email' => 'someone@example.com']);
});

it('returns false when unsubscribing an unknown email', function (): void {
    expect($this->service->unsubscribe('ghost@example.com'))->toBeFalse();
});
