<?php

declare(strict_types=1);

use AcMarche\CpasLibrary\Mail\ReminderMail;
use AcMarche\CpasLibrary\Models\Fiche;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
    config(['cpas-library.reminders.recipients' => ['library@example.com']]);
});

it('sends the reminder digest for fiches whose reminder date is today', function (): void {
    $today = Fiche::factory()->create(['date_rappel' => Carbon::today()]);
    Fiche::factory()->create(['date_rappel' => Carbon::today()->addDay()]);
    Fiche::factory()->create(['date_rappel' => null]);

    $this->artisan('cpas-library:reminder')->assertSuccessful();

    Mail::assertSent(ReminderMail::class, function (ReminderMail $mail) use ($today): bool {
        return $mail->hasTo('library@example.com')
            && $mail->fiches->contains($today)
            && $mail->fiches->count() === 1
            && $mail->urls[$today->id] !== '';
    });
});

it('does not send anything when there are no reminders today', function (): void {
    Fiche::factory()->create(['date_rappel' => Carbon::today()->addDay()]);

    $this->artisan('cpas-library:reminder')->assertSuccessful();

    Mail::assertNothingSent();
});

it('does not send anything when no recipients are configured', function (): void {
    config(['cpas-library.reminders.recipients' => []]);
    Fiche::factory()->create(['date_rappel' => Carbon::today()]);

    $this->artisan('cpas-library:reminder')->assertSuccessful();

    Mail::assertNothingSent();
});
