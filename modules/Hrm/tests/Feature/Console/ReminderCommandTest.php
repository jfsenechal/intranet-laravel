<?php

declare(strict_types=1);

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Filament\Resources\Evaluations\Pages\ViewEvaluation;
use AcMarche\Hrm\Mail\ReminderMail;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Employer;
use AcMarche\Hrm\Models\Evaluation;
use AcMarche\Hrm\Models\SmsReminder;
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
        'name' => 'Visite médicale',
        'reminder_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($deadline)
            && $mail->reminderType === 'Échéance'
            && $mail->recordName === 'Visite médicale'
            && str_contains($mail->subject, 'Visite médicale'),
    );
});

it('sends a deadline reminder to every department for an employee with no active contract', function (): void {
    Employer::factory()->create(['slug' => 'cpas']);

    config()->set('hrm.reminders.recipients.cpas', ['cpas@example.com']);

    // Only an inactive contract, so no department can be derived from it.
    $employee = Employee::factory()->create();
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'employer_id' => $this->employer->id,
        'is_closed' => true,
        'is_suspended' => true,
        'end_date' => Carbon::yesterday(),
    ]);

    $deadline = Deadline::factory()->create([
        'employee_id' => $employee->id,
        'reminder_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();
    $this->artisan('hrm:reminders', ['department' => 'cpas'])->assertSuccessful();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($deadline) && $mail->hasTo('rh@example.com'),
    );

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($deadline) && $mail->hasTo('cpas@example.com'),
    );
});

it('sends a deadline reminder for an employee with no contract at all', function (): void {
    $employee = Employee::factory()->create();

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

it('does not send a deadline to the department an employee left', function (): void {
    Employer::factory()->create(['slug' => 'cpas']);

    config()->set('hrm.reminders.recipients.cpas', ['cpas@example.com']);

    $employee = Employee::factory()->create();
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'employer_id' => Employer::query()->where('slug', 'cpas')->value('id'),
        'is_closed' => true,
        'is_suspended' => false,
        'end_date' => Carbon::yesterday(),
    ]);
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'employer_id' => $this->employer->id,
        'is_suspended' => false,
    ]);

    $deadline = Deadline::factory()->create([
        'employee_id' => $employee->id,
        'reminder_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'cpas'])->assertSuccessful();

    Mail::assertNotSent(ReminderMail::class);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($deadline) && $mail->reminderType === 'Échéance',
    );
});

it('does not send a deadline for an employee contracted to another department', function (): void {
    $otherEmployer = Employer::factory()->create(['slug' => 'cpas']);

    $employee = Employee::factory()->create();
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'employer_id' => $otherEmployer->id,
        'is_suspended' => false,
    ]);

    Deadline::factory()->create([
        'employee_id' => $employee->id,
        'reminder_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    Mail::assertNotSent(ReminderMail::class);
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

it('sends a contract reminder scoped to the employer of the contract itself', function (): void {
    $otherEmployer = Employer::factory()->create(['slug' => 'cpas']);

    // The employee moved from the other department to this one, so they still
    // have a closed contract there.
    $employee = Employee::factory()->create();
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'employer_id' => $otherEmployer->id,
        'is_closed' => true,
        'end_date' => Carbon::yesterday(),
    ]);

    $contract = Contract::factory()->create([
        'employee_id' => $employee->id,
        'employer_id' => $this->employer->id,
        'reminder_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($contract) && $mail->reminderType === 'Contrat',
    );
});

it('does not send a contract reminder to a department the contract does not belong to', function (): void {
    Employer::factory()->create(['slug' => 'cpas']);

    config()->set('hrm.reminders.recipients.cpas', ['cpas@example.com']);

    $employee = Employee::factory()->create();
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'employer_id' => Employer::query()->where('slug', 'cpas')->value('id'),
        'is_closed' => true,
        'end_date' => Carbon::yesterday(),
    ]);

    Contract::factory()->create([
        'employee_id' => $employee->id,
        'employer_id' => $this->employer->id,
        'reminder_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'cpas'])->assertSuccessful();

    Mail::assertNotSent(ReminderMail::class);
});

it('sends a contract reminder for a contract of a child employer', function (): void {
    $child = Employer::factory()->create([
        'slug' => 'ville-enseignement',
        'parent_id' => $this->employer->id,
    ]);

    $contract = Contract::factory()->create([
        'employer_id' => $child->id,
        'reminder_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($contract) && $mail->reminderType === 'Contrat',
    );
});

it('sends an evaluation reminder linking to the evaluation itself', function (): void {
    $employee = Employee::factory()->create(['status' => StatusEnum::AGENT]);
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'employer_id' => $this->employer->id,
        'is_suspended' => false,
    ]);

    $evaluation = Evaluation::factory()->create([
        'employee_id' => $employee->id,
        'next_evaluation_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($evaluation)
            && $mail->reminderType === 'Évaluation'
            && $mail->url === ViewEvaluation::getUrl(['record' => $evaluation], panel: 'hrm-panel'),
    );
});

it('does not send an evaluation reminder whose next date is not today', function (): void {
    $employee = Employee::factory()->create(['status' => StatusEnum::AGENT]);

    Evaluation::factory()->create([
        'employee_id' => $employee->id,
        'next_evaluation_date' => Carbon::tomorrow(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    Mail::assertNotSent(ReminderMail::class);
});

it('leaves sms reminders to the sms command', function (): void {
    $sms = SmsReminder::factory()->create([
        'employee_id' => null,
        'reminder_date' => Carbon::today(),
    ]);

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    expect($sms->refresh()->result)->toBeNull()
        ->and($sms->sent_at)->toBeNull();
});
