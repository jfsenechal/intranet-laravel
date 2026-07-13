<?php

declare(strict_types=1);

use AcMarche\Hrm\Mail\ReminderMail;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Employer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();

    config()->set('hrm.reminders.recipients.ville', ['rh@example.com']);

    $this->employer = Employer::factory()->create(['slug' => 'ville']);
});

it('sends a deadline reminder for an employee with an active contract', function (): void {
    $employee = Employee::factory()->create();
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'employer_id' => $this->employer->id,
        'is_suspended' => false,
    ]);

    $deadline = Deadline::factory()->create([
        'employee_id' => $employee->id,
        'reminder_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($deadline) && $mail->reminderType === 'Échéance',
    );
});

it('sends a deadline reminder for a deadline without an employee scoped to the department', function (): void {
    $deadline = Deadline::factory()->create([
        'employee_id' => null,
        'employer_id' => $this->employer->id,
        'reminder_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($deadline)
            && $mail->reminderType === 'Échéance'
            && $mail->employeeName === null,
    );
});

it('does not send a deadline without an employee that belongs to another department', function (): void {
    $otherEmployer = Employer::factory()->create(['slug' => 'cpas']);

    Deadline::factory()->create([
        'employee_id' => null,
        'employer_id' => $otherEmployer->id,
        'reminder_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    Mail::assertNotSent(ReminderMail::class);
});

it('does not send a deadline whose reminder date is not today', function (): void {
    Deadline::factory()->create([
        'employee_id' => null,
        'employer_id' => $this->employer->id,
        'reminder_date' => Carbon::tomorrow(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    Mail::assertNotSent(ReminderMail::class);
});
