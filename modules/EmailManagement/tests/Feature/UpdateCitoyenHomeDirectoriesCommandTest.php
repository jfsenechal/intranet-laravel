<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Models\Citoyen;

it('rewrites the home directory to the Dovecot path', function (string $mail, string $expected): void {
    $citoyen = Citoyen::factory()->create(['mail' => $mail, 'homeDirectory' => '/var/spool/dovecot/mail/s/sonia.d']);

    $this->artisan('email-management:update-citoyen-home-directories')->assertSuccessful();

    expect($citoyen->refresh()->homeDirectory)->toBe($expected);
})->with([
    'lowercase' => ['sonia.d@marche.be', '/home/vmail/marche.be/s/sonia.d@marche.be'],
    'uppercase first letter, as Dovecot lowers it' => ['Sonia.d@marche.be', '/home/vmail/marche.be/s/Sonia.d@marche.be'],
]);

it('changes nothing with the dry run option', function (): void {
    $citoyen = Citoyen::factory()->create(['homeDirectory' => '/var/spool/dovecot/mail/s/sonia.d']);

    $this->artisan('email-management:update-citoyen-home-directories', ['--dry-run' => true])
        ->expectsOutputToContain('1 citoyens sur 1 à mettre à jour')
        ->assertSuccessful();

    expect($citoyen->refresh()->homeDirectory)->toBe('/var/spool/dovecot/mail/s/sonia.d');
});

it('skips the rows already up to date and keeps updated_at', function (): void {
    $this->travelTo(now()->subDay());
    $outdated = Citoyen::factory()->create(['homeDirectory' => '/var/spool/dovecot/mail/s/sonia.d']);
    Citoyen::factory()->create();
    $updatedAt = $outdated->refresh()->updated_at->toDateTimeString();
    $this->travelBack();

    $this->artisan('email-management:update-citoyen-home-directories')
        ->expectsOutputToContain('1 citoyens sur 2 mis à jour')
        ->assertSuccessful();

    expect($outdated->refresh()->updated_at->toDateTimeString())->toBe($updatedAt);

    $this->artisan('email-management:update-citoyen-home-directories')
        ->expectsOutputToContain('0 citoyens sur 2 mis à jour')
        ->assertSuccessful();
});
