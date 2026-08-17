<?php

declare(strict_types=1);

use AcMarche\AldermenAgenda\Mail\EventEmail;
use AcMarche\AldermenAgenda\Models\Event;
use Illuminate\Mail\Mailables\Attachment;

it('attaches the event uploaded files and not the logo', function (): void {
    $event = new Event([
        'name' => 'Manifestation',
        'file1_name' => 'programme.pdf',
        'file2_name' => 'plan.pdf',
    ]);

    $attachments = (new EventEmail($event))->attachments();

    expect($attachments)
        ->toHaveCount(2)
        ->each->toBeInstanceOf(Attachment::class);
});

it('has no attachments when the event has no files', function (): void {
    $event = new Event(['name' => 'Manifestation']);

    expect((new EventEmail($event))->attachments())->toBeEmpty();
});

it('attaches only the files that are present', function (): void {
    $event = new Event([
        'name' => 'Manifestation',
        'file1_name' => 'programme.pdf',
    ]);

    expect((new EventEmail($event))->attachments())->toHaveCount(1);
});

it('renders the description line breaks as html', function (): void {
    $event = new Event([
        'name' => 'Manifestation',
        'description' => "Première ligne\nDeuxième ligne",
    ]);

    (new EventEmail($event))->assertSeeInHtml('Première ligne<br', escape: false);
});

it('escapes html in the description', function (): void {
    $event = new Event([
        'name' => 'Manifestation',
        'description' => '<script>alert(1)</script>',
    ]);

    (new EventEmail($event))->assertDontSeeInHtml('<script>alert(1)</script>', escape: false);
});
