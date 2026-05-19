<?php

declare(strict_types=1);

use AcMarche\Ad\Mail\ClassifiedAdEmail;
use AcMarche\Ad\Models\ClassifiedAd;
use AcMarche\Ad\Models\Subscriber;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
});

it('sends an email to each subscriber when a classified ad is created', function (): void {
    Subscriber::factory()->create(['email' => 'one@example.com']);
    Subscriber::factory()->create(['email' => 'two@example.com']);

    ClassifiedAd::factory()->create();

    Mail::assertQueuedCount(0); // sync driver in tests
    Mail::assertSent(ClassifiedAdEmail::class, fn (ClassifiedAdEmail $mail): bool => $mail->hasTo('one@example.com'));
    Mail::assertSent(ClassifiedAdEmail::class, fn (ClassifiedAdEmail $mail): bool => $mail->hasTo('two@example.com'));
});

it('does not send subscriber emails when there are no subscribers', function (): void {
    ClassifiedAd::factory()->create();

    Mail::assertNotSent(ClassifiedAdEmail::class, fn (ClassifiedAdEmail $mail): bool => $mail->hasTo('one@example.com'));
});
