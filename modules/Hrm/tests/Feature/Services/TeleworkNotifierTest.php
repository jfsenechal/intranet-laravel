<?php

declare(strict_types=1);

use AcMarche\Hrm\Mail\TeleworkEmployeeSubmittedMail;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Direction;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Telework;
use AcMarche\Hrm\Services\TeleworkNotifier;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/**
 * `professional_email` is intentionally not mass-assignable on Employee, so it
 * is set outside the factory attributes.
 */
function requester(string $username, ?string $email): Employee
{
    $employee = Employee::factory()->create(['username' => $username]);

    $employee->professional_email = $email;
    $employee->save();

    return $employee;
}

/**
 * Telework stamps `user_add` from the authenticated user via HasUserAdd, so the
 * requester must be the acting user for the record to belong to them.
 */
function submitRequestFor(Employee $employee): Telework
{
    test()->actingAs(User::factory()->create(['username' => $employee->username]));

    return Telework::factory()->create();
}

it('emails the requester when the request is submitted', function (): void {
    Mail::fake();

    $employee = requester('jdoe', 'jdoe@marche.be');
    $telework = submitRequestFor($employee);

    TeleworkNotifier::notifyEmployeeOfSubmission($telework);

    Mail::assertQueued(
        TeleworkEmployeeSubmittedMail::class,
        fn (TeleworkEmployeeSubmittedMail $mail): bool => $mail->hasTo('jdoe@marche.be')
            && $mail->telework->is($telework)
            && $mail->employee->is($employee),
    );
});

it('names the director in the submission confirmation when one is resolvable', function (): void {
    Mail::fake();

    $director = requester('bmartin', 'bmartin@marche.be');
    $employee = requester('jdoe', 'jdoe@marche.be');

    $direction = Direction::factory()->create(['director' => $director->username]);
    Contract::factory()->create([
        'employee_id' => $employee->getKey(),
        'direction_id' => $direction->getKey(),
        'is_closed' => false,
        'is_suspended' => false,
        'end_date' => null,
    ]);

    $telework = submitRequestFor($employee);

    TeleworkNotifier::notifyEmployeeOfSubmission($telework);

    Mail::assertQueued(
        TeleworkEmployeeSubmittedMail::class,
        fn (TeleworkEmployeeSubmittedMail $mail): bool => $mail->director?->is($director) === true,
    );
});

it('still confirms the submission when no director is resolvable', function (): void {
    Mail::fake();

    $employee = requester('jdoe', 'jdoe@marche.be');
    $telework = submitRequestFor($employee);

    TeleworkNotifier::notifyEmployeeOfSubmission($telework);

    Mail::assertQueued(
        TeleworkEmployeeSubmittedMail::class,
        fn (TeleworkEmployeeSubmittedMail $mail): bool => $mail->director === null,
    );
});

it('renders the submission confirmation with the request summary and link', function (): void {
    $director = requester('bmartin', 'bmartin@marche.be');
    $employee = requester('jdoe', 'jdoe@marche.be');
    $telework = submitRequestFor($employee);

    $html = (new TeleworkEmployeeSubmittedMail($telework, $employee, $director))->render();

    expect($html)
        ->toContain($employee->first_name)
        ->toContain('enregistrée')
        ->toContain($director->last_name)
        ->toContain($telework->location_type->getLabel())
        ->toContain('/my-space/telework-page');
});

it('does not email a requester without a professional address', function (): void {
    Mail::fake();

    $employee = requester('jdoe', null);
    $telework = submitRequestFor($employee);

    TeleworkNotifier::notifyEmployeeOfSubmission($telework);

    Mail::assertNothingQueued();
});
