<?php

declare(strict_types=1);

use AcMarche\App\Sms\InforiusClient;
use AcMarche\Hrm\Mail\ReminderMail;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Employer;
use AcMarche\Hrm\Models\SmsReminder;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();

    config()->set('hrm.reminders.recipients.ville', ['rh@example.com']);

    $this->employer = Employer::factory()->create(['slug' => 'ville']);
});

/**
 * An employee contracted to the department the reminders are sent for.
 */
function employeeInDepartment(Employer $employer): Employee
{
    $employee = Employee::factory()->create();

    Contract::factory()->create([
        'employee_id' => $employee->id,
        'employer_id' => $employer->id,
    ]);

    return $employee;
}

/**
 * InforiusClient is final, so the gateway is faked at the HTTP layer instead.
 * The token call always succeeds; $sendResponse decides the Send outcome.
 */
function fakeSmsGateway(mixed $sendResponse): HttpFactory
{
    $http = new HttpFactory;
    $http->fake([
        'sms.example.test/RequestToken' => HttpFactory::response(
            <<<'XML'
<RequestTokenResponse xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error i:nil="true"/>
    <Expiration>1200000</Expiration>
    <Token>token-xyz</Token>
</RequestTokenResponse>
XML
        ),
        'sms.example.test/Send' => $sendResponse,
    ]);

    app()->instance(InforiusClient::class, new InforiusClient(
        host: 'https://sms.example.test/',
        user: 'test_user',
        password: 'secret',
        http: $http,
    ));

    return $http;
}

function successfulSendResponse(): mixed
{
    return HttpFactory::response(
        <<<'XML'
<SendMessageResponse xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error i:nil="true"/>
    <Balance>500</Balance>
    <Messages>
        <MessageStatus>
            <ErrorCode i:nil="true"/>
            <ErrorMessage i:nil="true"/>
            <Number>+32476123456</Number>
            <Type>S</Type>
        </MessageStatus>
    </Messages>
</SendMessageResponse>
XML
    );
}

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

it('sends a deadline reminder for an employee whose contract is no longer active', function (): void {
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

it('sends an sms reminder through the gateway and records the result', function (): void {
    $employee = employeeInDepartment($this->employer);

    $sms = SmsReminder::factory()->create([
        'employee_id' => $employee->id,
        'phone_number' => '32476123456',
        'message' => 'Rappel visite médicale',
        'reminder_date' => Carbon::today(),
    ]);

    $http = fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    $http->assertSent(fn ($request): bool => str_contains((string) $request->url(), '/Send')
        && str_contains($request->body(), urlencode('+32476123456'))
        && str_contains($request->body(), 'reminder-32476123456'));

    $sms->refresh();

    expect($sms->result)->toBe('OK')
        ->and($sms->sent_at)->not->toBeNull();

    Mail::assertNotSent(ReminderMail::class);
});

it('sends an sms reminder due on its other reminder date', function (): void {
    $employee = employeeInDepartment($this->employer);

    $sms = SmsReminder::factory()->create([
        'employee_id' => $employee->id,
        'reminder_date' => Carbon::tomorrow(),
        'other_reminder_date' => Carbon::today(),
    ]);

    fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    expect($sms->refresh()->result)->toBe('OK');
});

it('sends an sms reminder that has no employee attached', function (): void {
    $sms = SmsReminder::factory()->create([
        'employee_id' => null,
        'reminder_date' => Carbon::today(),
    ]);

    fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    expect($sms->refresh()->result)->toBe('OK');

    Mail::assertNotSent(ReminderMail::class);
});

it('does not send an sms reminder twice on the same day', function (): void {
    $sms = SmsReminder::factory()->create([
        'employee_id' => null,
        'reminder_date' => Carbon::today(),
        'sent_at' => Carbon::today(),
    ]);

    $http = fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    $http->assertNotSent(fn ($request): bool => str_contains((string) $request->url(), '/Send'));

    expect($sms->refresh()->result)->toBeNull();
});

it('mails the recipients when the gateway rejects the sms', function (): void {
    $employee = employeeInDepartment($this->employer);

    $sms = SmsReminder::factory()->create([
        'employee_id' => $employee->id,
        'reminder_date' => Carbon::today(),
    ]);

    fakeSmsGateway(HttpFactory::response(
        <<<'XML'
<SendMessageResponse xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
    <Error i:nil="true"/>
    <Balance>500</Balance>
    <Messages>
        <MessageStatus>
            <ErrorCode>12</ErrorCode>
            <ErrorMessage>Numéro invalide</ErrorMessage>
            <Number>+32476123456</Number>
            <Type>S</Type>
        </MessageStatus>
    </Messages>
</SendMessageResponse>
XML
    ));

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    $sms->refresh();

    expect($sms->result)->toBe('Numéro invalide')
        ->and($sms->sent_at)->toBeNull();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($sms) && $mail->reminderType === 'SMS',
    );
});

it('mails the recipients when the gateway is unreachable', function (): void {
    $employee = employeeInDepartment($this->employer);

    $sms = SmsReminder::factory()->create([
        'employee_id' => $employee->id,
        'reminder_date' => Carbon::today(),
    ]);

    fakeSmsGateway(HttpFactory::response('', 500));

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    $sms->refresh();

    expect($sms->result)->toContain('HTTP 500')
        ->and($sms->sent_at)->toBeNull();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($sms) && $mail->reminderType === 'SMS',
    );
});

it('does not call the gateway for an sms reminder with an empty message', function (): void {
    $employee = employeeInDepartment($this->employer);

    $sms = SmsReminder::factory()->create([
        'employee_id' => $employee->id,
        'message' => '   ',
        'reminder_date' => Carbon::today(),
    ]);

    $http = fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:reminders', ['department' => 'ville'])->assertSuccessful();

    $http->assertNothingSent();

    expect($sms->refresh()->result)->toBe('Numéro et message obligatoires');

    Mail::assertSent(ReminderMail::class);
});
