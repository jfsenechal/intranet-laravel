<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\IncomingMail;

it('redirects the legacy indicateur courrier url to the new view page', function (): void {
    $mail = IncomingMail::factory()->create();

    $target = route('filament.courrier-panel.resources.incoming-mails.view', ['record' => $mail->id]);

    $this->get("/ville/indicateur/courrier/{$mail->id}")
        ->assertRedirect($target)
        ->assertStatus(301);
});

it('matches every department segment for the legacy courrier url', function (string $department): void {
    $mail = IncomingMail::factory()->create();

    $this->get("/{$department}/indicateur/courrier/{$mail->id}")
        ->assertRedirect(route('filament.courrier-panel.resources.incoming-mails.view', ['record' => $mail->id]));
})->with(['ville', 'cpas', 'bgm']);

it('does not match a non-numeric legacy courrier id', function (): void {
    $this->get('/ville/indicateur/courrier/abc')
        ->assertNotFound();
});
