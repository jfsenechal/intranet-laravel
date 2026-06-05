<?php

declare(strict_types=1);

use AcMarche\Courrier\Jobs\IndexIncomingMailJob;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Repository\DepartmentScope;
use Illuminate\Support\Facades\Queue;

it('queues an index job when an incoming mail is created', function (): void {
    Queue::fake();

    $mail = IncomingMail::factory()->create();

    Queue::assertPushed(
        IndexIncomingMailJob::class,
        fn (IndexIncomingMailJob $job): bool => $job->incomingMailId === $mail->id,
    );
});

it('queues an index job when an incoming mail is deleted', function (): void {
    $mail = IncomingMail::factory()->create();

    Queue::fake();
    $mail->delete();

    Queue::assertPushed(
        IndexIncomingMailJob::class,
        fn (IndexIncomingMailJob $job): bool => $job->incomingMailId === $mail->id,
    );
});

it('resolves a soft-deleted mail to null so the job removes it from the index', function (): void {
    $mail = IncomingMail::factory()->create();
    $mail->delete();

    // Mirrors the job's lookup: department scope dropped, soft-delete scope kept,
    // so a trashed mail is null and the job falls through to deleteMail().
    $found = IncomingMail::query()
        ->withoutGlobalScope(DepartmentScope::class)
        ->find($mail->id);

    expect($found)->toBeNull();
});

it('does nothing and does not throw when Meilisearch is not configured', function (): void {
    config()->set('app.meilisearch.master_key', null);

    $mail = IncomingMail::factory()->create();

    (new IndexIncomingMailJob($mail->id))->handle();
})->throwsNoExceptions();
