<?php

declare(strict_types=1);

use AcMarche\App\Sms\InforiusClient;
use AcMarche\Hrm\Mail\ReminderMail;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\SmsReminder;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();

    config()->set('hrm.reminders.recipients.ville', ['rh@example.com']);
    config()->set('hrm.reminders.recipients.cpas', ['cpas@example.com']);
});

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

it('sends an sms reminder through the gateway and records the result', function (): void {
    $sms = SmsReminder::factory()->create([
        'employee_id' => Employee::factory(),
        'phone_number' => '32476123456',
        'message' => 'Rappel visite médicale',
        'reminder_date' => Carbon::today(),
    ]);

    $http = fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:sms-reminders')->assertSuccessful();

    $http->assertSent(fn ($request): bool => str_contains((string) $request->url(), '/Send')
        && str_contains($request->body(), urlencode('+32476123456'))
        && str_contains($request->body(), 'reminder-32476123456'));

    $sms->refresh();

    expect($sms->result)->toBe('OK')
        ->and($sms->sent_at)->not->toBeNull();

    Mail::assertNotSent(ReminderMail::class);
});

it('sends an sms reminder due on its other reminder date', function (): void {
    $sms = SmsReminder::factory()->create([
        'employee_id' => Employee::factory(),
        'reminder_date' => Carbon::tomorrow(),
        'other_reminder_date' => Carbon::today(),
    ]);

    fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:sms-reminders')->assertSuccessful();

    expect($sms->refresh()->result)->toBe('OK');
});

it('sends an sms reminder that has no employee attached', function (): void {
    $sms = SmsReminder::factory()->create([
        'employee_id' => null,
        'reminder_date' => Carbon::today(),
    ]);

    fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:sms-reminders')->assertSuccessful();

    expect($sms->refresh()->result)->toBe('OK');

    Mail::assertNotSent(ReminderMail::class);
});

it('does not send an sms reminder whose reminder date is not today', function (): void {
    $sms = SmsReminder::factory()->create([
        'employee_id' => null,
        'reminder_date' => Carbon::tomorrow(),
        'other_reminder_date' => null,
    ]);

    $http = fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:sms-reminders')->assertSuccessful();

    $http->assertNothingSent();

    expect($sms->refresh()->result)->toBeNull();
});

it('does not send an sms reminder twice on the same day', function (): void {
    $sms = SmsReminder::factory()->create([
        'employee_id' => null,
        'reminder_date' => Carbon::today(),
        'sent_at' => Carbon::today(),
    ]);

    $http = fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:sms-reminders')->assertSuccessful();

    $http->assertNotSent(fn ($request): bool => str_contains((string) $request->url(), '/Send'));

    expect($sms->refresh()->result)->toBeNull();
});

it('mails every configured hr mailbox when the gateway rejects the sms', function (): void {
    $sms = SmsReminder::factory()->create([
        'employee_id' => Employee::factory(),
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

    $this->artisan('hrm:sms-reminders')->assertSuccessful();

    $sms->refresh();

    expect($sms->result)->toBe('Numéro invalide')
        ->and($sms->sent_at)->toBeNull();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($sms)
            && $mail->reminderType === 'SMS'
            && $mail->hasTo('rh@example.com')
            && $mail->hasTo('cpas@example.com'),
    );
});

it('mails the hr mailboxes when the gateway is unreachable', function (): void {
    $sms = SmsReminder::factory()->create([
        'employee_id' => Employee::factory(),
        'reminder_date' => Carbon::today(),
    ]);

    fakeSmsGateway(HttpFactory::response('', 500));

    $this->artisan('hrm:sms-reminders')->assertSuccessful();

    $sms->refresh();

    expect($sms->result)->toContain('HTTP 500')
        ->and($sms->sent_at)->toBeNull();

    Mail::assertSent(
        ReminderMail::class,
        fn (ReminderMail $mail): bool => $mail->record->is($sms) && $mail->reminderType === 'SMS',
    );
});

it('does not call the gateway for an sms reminder with an empty message', function (): void {
    $sms = SmsReminder::factory()->create([
        'employee_id' => Employee::factory(),
        'message' => '   ',
        'reminder_date' => Carbon::today(),
    ]);

    $http = fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:sms-reminders')->assertSuccessful();

    $http->assertNothingSent();

    expect($sms->refresh()->result)->toBe('Numéro et message obligatoires');

    Mail::assertSent(ReminderMail::class);
});

it('records the failure without mailing when no recipient is configured', function (): void {
    config()->set('hrm.reminders.recipients', []);

    $sms = SmsReminder::factory()->create([
        'employee_id' => null,
        'message' => '   ',
        'reminder_date' => Carbon::today(),
    ]);

    fakeSmsGateway(successfulSendResponse());

    $this->artisan('hrm:sms-reminders')->assertSuccessful();

    expect($sms->refresh()->result)->toBe('Numéro et message obligatoires');

    Mail::assertNothingSent();
});
