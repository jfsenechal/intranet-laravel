<?php

declare(strict_types=1);

use AcMarche\Ad\Models\Subscriber;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\post;

it('shows the public subscription page', function (): void {
    $this->get(route('ad.subscription.show'))
        ->assertOk()
        ->assertSee("S'abonner");
});

it('subscribes a valid agent through the public route', function (): void {
    $employee = Employee::factory()->create([
        'first_name' => 'Diane',
        'last_name' => 'Petit',
        'professional_email' => 'diane@marche.be',
        'status' => StatusEnum::AGENT->value,
    ]);
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => null,
    ]);

    post(route('ad.subscription.subscribe'), ['email' => 'diane@marche.be'])
        ->assertSessionHas('success');

    assertDatabaseHas(Subscriber::class, [
        'email' => 'diane@marche.be',
        'first_name' => 'Diane',
        'last_name' => 'Petit',
    ]);
});

it('redirects with an error when the email is unknown', function (): void {
    post(route('ad.subscription.subscribe'), ['email' => 'nobody@example.com'])
        ->assertSessionHas('error');

    assertDatabaseMissing(Subscriber::class, ['email' => 'nobody@example.com']);
});

it('unsubscribes through the public route', function (): void {
    Subscriber::factory()->create(['email' => 'leave@example.com']);

    post(route('ad.subscription.unsubscribe'), ['email' => 'leave@example.com'])
        ->assertSessionHas('success');

    assertDatabaseMissing(Subscriber::class, ['email' => 'leave@example.com']);
});

it('validates the email field', function (): void {
    post(route('ad.subscription.subscribe'), ['email' => 'not-an-email'])
        ->assertSessionHasErrors('email');
});
