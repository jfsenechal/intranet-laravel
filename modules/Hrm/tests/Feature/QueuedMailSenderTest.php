<?php

declare(strict_types=1);

use AcMarche\Agent\Mail\ProfileRequestMail;
use AcMarche\Hrm\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
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
