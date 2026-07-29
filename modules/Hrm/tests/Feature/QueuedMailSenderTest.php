<?php

declare(strict_types=1);

use AcMarche\Agent\Mail\ProfileRequestMail;
use AcMarche\Hrm\Mail\TeleworkEmployeeHrResultMail;
use AcMarche\Hrm\Mail\TeleworkEmployeeManagerResultMail;
use AcMarche\Hrm\Mail\TeleworkEmployeeSubmittedMail;
use AcMarche\Hrm\Mail\TeleworkHrValidationMail;
use AcMarche\Hrm\Mail\TeleworkManagerValidationMail;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Telework;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Auth;

it('marks the mailable as queued', function (): void {
    $employee = Employee::factory()->create();

    expect(new ProfileRequestMail($employee))->toBeInstanceOf(ShouldQueue::class);
});

it('captures the authenticated sender at construction so it survives the queue', function (): void {
    $user = User::factory()->create();
    $employee = Employee::factory()->create();

    $this->actingAs($user);

    $mail = new ProfileRequestMail($employee);

    // Simulate the queue worker: no authenticated user when the job runs.
    Auth::logout();

    $from = $mail->envelope()->from;

    expect($from)->not->toBeNull()
        ->and($from->address)->toBe($user->email)
        ->and($from->name)->toBe($user->fullNameAsString());
});

/**
 * A queue worker has no current Filament panel, so `getUrl()` would fall back to
 * the default panel and throw RouteNotFoundException. Every telework mail has to
 * name its panel explicitly.
 *
 * The mails addressed to staff link into the hrm-panel, where the validation
 * pages live and only HRM roles can reach.
 */
it('builds validation links against the hrm panel with no current panel', function (
    Mailable $mail,
    string $expectedPath,
): void {
    // Simulate the queue worker: no authenticated user, no current Filament panel.
    Auth::logout();
    Filament::setCurrentPanel(null);

    $url = $mail->content()->with['url'];

    expect($url)->toContain('/hrm/teleworks/')
        ->and($url)->toEndWith($expectedPath);
})->with([
    'manager validation' => fn (): array => [
        new TeleworkManagerValidationMail(
            $telework = Telework::factory()->create(),
            Employee::factory()->create(),
            Employee::factory()->create(),
        ),
        "/{$telework->getKey()}/manager-validate",
    ],
    'hr validation' => fn (): array => [
        new TeleworkHrValidationMail(
            $telework = Telework::factory()->create(),
            Employee::factory()->create(),
        ),
        "/{$telework->getKey()}/hr-validate",
    ],
]);

/**
 * The mails addressed to the agent link to their own page in the app-panel, not
 * into the hrm-panel: TeleworkPage resolves the record from the authenticated
 * user, so it needs no record parameter.
 */
it('points the agent at their own telework page with no current panel', function (Mailable $mail): void {
    Auth::logout();
    Filament::setCurrentPanel(null);

    expect($mail->content()->with['url'])->toEndWith('/my-space/telework-page');
})->with([
    'submission' => fn (): Mailable => new TeleworkEmployeeSubmittedMail(
        Telework::factory()->create(),
        Employee::factory()->create(),
    ),
    'employee manager result' => fn (): Mailable => new TeleworkEmployeeManagerResultMail(
        Telework::factory()->create(),
        Employee::factory()->create(),
    ),
    'employee hr result' => fn (): Mailable => new TeleworkEmployeeHrResultMail(
        Telework::factory()->create(),
        Employee::factory()->create(),
    ),
]);
