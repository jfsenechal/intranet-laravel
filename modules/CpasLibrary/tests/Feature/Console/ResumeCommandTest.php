<?php

declare(strict_types=1);

use AcMarche\CpasLibrary\Mail\ResumeMail;
use AcMarche\CpasLibrary\Models\Fiche;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
    config(['cpas-library.reminders.recipients' => ['library@example.com']]);
});

it('sends the weekly digest for fiches added over the last seven days', function (): void {
    $recent = Fiche::factory()->create(['createdAt' => Carbon::today()->subDays(2)]);
    Fiche::factory()->create(['createdAt' => Carbon::today()->subDays(30)]);

    $this->artisan('cpas-library:resume')->assertSuccessful();

    Mail::assertSent(ResumeMail::class, function (ResumeMail $mail) use ($recent): bool {
        return $mail->hasTo('library@example.com')
            && $mail->fiches->contains($recent)
            && $mail->fiches->count() === 1
            && $mail->urls[$recent->id] !== '';
    });
});

it('does not send anything when no fiches were added in the last seven days', function (): void {
    Fiche::factory()->create(['createdAt' => Carbon::today()->subDays(30)]);

    $this->artisan('cpas-library:resume')->assertSuccessful();

    Mail::assertNothingSent();
});

it('does not send anything when no recipients are configured', function (): void {
    config(['cpas-library.reminders.recipients' => []]);
    Fiche::factory()->create(['createdAt' => Carbon::today()]);

    $this->artisan('cpas-library:resume')->assertSuccessful();

    Mail::assertNothingSent();
});
