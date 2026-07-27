<?php

declare(strict_types=1);

use AcMarche\Agent\Mail\ProfileRequestMail;
use AcMarche\Hrm\Mail\TeleworkEmployeeHrResultMail;
use AcMarche\Hrm\Mail\TeleworkEmployeeManagerResultMail;
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
 * The Telework pages live in the `hrm-panel`, but a queue worker has no current
 * Filament panel, so `getUrl()` would fall back to the default `app-panel` and
 * throw RouteNotFoundException. The panel must be named explicitly.
 */
it('builds telework links against the hrm panel with no current panel', function (
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
    'employee manager result' => fn (): array => [
        new TeleworkEmployeeManagerResultMail(
            $telework = Telework::factory()->create(),
            Employee::factory()->create(),
        ),
        "/{$telework->getKey()}/view",
    ],
    'employee hr result' => fn (): array => [
        new TeleworkEmployeeHrResultMail(
            $telework = Telework::factory()->create(),
            Employee::factory()->create(),
        ),
        "/{$telework->getKey()}/view",
    ],
]);
