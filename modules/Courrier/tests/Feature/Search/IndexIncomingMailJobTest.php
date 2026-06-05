<?php

declare(strict_types=1);

use AcMarche\Courrier\Jobs\IndexIncomingMailJob;
use AcMarche\Courrier\Models\IncomingMail;
use Illuminate\Support\Facades\Queue;

it('queues an index job when an incoming mail is created', function (): void {
    Queue::fake();

    $mail = IncomingMail::factory()->create();

    Queue::assertPushed(
        IndexIncomingMailJob::class,
        fn (IndexIncomingMailJob $job): bool => $job->incomingMailId === $mail->id,
    );
});

it('does nothing and does not throw when Meilisearch is not configured', function (): void {
    config()->set('app.meilisearch.master_key', null);

    $mail = IncomingMail::factory()->create();

    (new IndexIncomingMailJob($mail->id))->handle();
})->throwsNoExceptions();
