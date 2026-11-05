<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Models\VirtualUser;
use Illuminate\Support\Facades\File;

beforeEach(function (): void {
    $this->mailRoot = sys_get_temp_dir().'/citoyen-new-mail-'.uniqid();
    File::makeDirectory($this->mailRoot);
    config()->set('email-management.mail_root', $this->mailRoot);

    $this->deliverMail = function (Citoyen $citoyen, int $daysAgo): void {
        $path = Citoyen::homeDirectoryFor($citoyen->mail).'/Maildir/new';
        File::ensureDirectoryExists($path);
        File::put($path.'/'.now()->subDays($daysAgo)->timestamp.'.M'.uniqid().'P1.mail', 'message');
    };

    $this->writeSieve = function (Citoyen $citoyen, string $script): void {
        $path = Citoyen::homeDirectoryFor($citoyen->mail).'/sieve';
        File::ensureDirectoryExists($path);
        File::put($path.'/roundcube.sieve', $script);
    };
});

afterEach(function (): void {
    File::deleteDirectory($this->mailRoot);
});

it('stops when the mail store cannot be read', function (): void {
    Citoyen::factory()->create();
    config()->set('email-management.mail_root', $this->mailRoot.'/missing');

    $this->artisan('email-management:citoyen-new-mail')
        ->expectsOutputToContain("n'est pas lisible")
        ->assertFailed();
});

it('lists the unread mail, the longest waiting first', function (): void {
    $recent = Citoyen::factory()->create(['mail' => 'recent@marche.be']);
    $old = Citoyen::factory()->create(['mail' => 'old@marche.be']);
    ($this->deliverMail)($recent, 2);
    ($this->deliverMail)($old, 40);
    ($this->deliverMail)($old, 10);

    $this->artisan('email-management:citoyen-new-mail')
        ->expectsTable(['mail', 'non lus', 'plus ancien', 'en attente depuis'], [
            ['old@marche.be', 2, now()->subDays(40)->format('d/m/Y'), '40 j'],
            ['recent@marche.be', 1, now()->subDays(2)->format('d/m/Y'), '2 j'],
        ])
        ->expectsOutputToContain('2 compte(s), 3 message(s) non lu(s).')
        ->assertSuccessful();
});

it('filters on the keyword, the unread mail and its age', function (): void {
    $old = Citoyen::factory()->create(['mail' => 'old@marche.be']);
    $recent = Citoyen::factory()->create(['mail' => 'recent@marche.be']);
    Citoyen::factory()->create(['mail' => 'empty@marche.be']);
    ($this->deliverMail)($old, 40);
    ($this->deliverMail)($recent, 2);

    $this->artisan('email-management:citoyen-new-mail', ['keyword' => 'rece'])
        ->expectsOutputToContain('1 compte(s), 1 message(s)')
        ->assertSuccessful();

    $this->artisan('email-management:citoyen-new-mail', ['--only-with-mail' => true])
        ->expectsOutputToContain('2 compte(s), 2 message(s)')
        ->assertSuccessful();

    $this->artisan('email-management:citoyen-new-mail', ['--min-days' => 30])
        ->expectsOutputToContain('1 compte(s), 1 message(s)')
        ->assertSuccessful();
});

it('refuses to delete without a minimum age', function (array $options): void {
    $citoyen = Citoyen::factory()->create();
    ($this->deliverMail)($citoyen, 40);

    $this->artisan('email-management:citoyen-new-mail', ['--delete' => true, ...$options])
        ->expectsOutputToContain('--min-days')
        ->assertFailed();

    expect(Citoyen::query()->count())->toBe(1);
})->with([
    'no option' => [[]],
    'zero days' => [['--min-days' => 0]],
]);

it('deletes the citoyen and its login once confirmed, leaving the mailbox on disk', function (): void {
    $citoyen = Citoyen::factory()->create(['mail' => 'old@marche.be']);
    ($this->deliverMail)($citoyen, 40);

    $this->artisan('email-management:citoyen-new-mail', ['--min-days' => 30, '--delete' => true])
        ->expectsConfirmation('Supprimer le compte old@marche.be ?', 'yes')
        ->expectsOutputToContain('1 supprimé(s)')
        ->assertSuccessful();

    expect(Citoyen::query()->count())->toBe(0)
        ->and(VirtualUser::query()->count())->toBe(0)
        ->and(File::isDirectory(Citoyen::homeDirectoryFor('old@marche.be').'/Maildir'))->toBeTrue();
});

it('keeps the account without confirmation, even without a Sieve script', function (): void {
    $citoyen = Citoyen::factory()->create(['mail' => 'old@marche.be']);
    ($this->deliverMail)($citoyen, 40);

    $this->artisan('email-management:citoyen-new-mail', ['--min-days' => 30, '--delete' => true])
        ->expectsConfirmation('Supprimer le compte old@marche.be ?', 'no')
        ->expectsOutputToContain('1 conservé(s)')
        ->assertSuccessful();

    expect(Citoyen::query()->count())->toBe(1);
});

it('keeps the account when it cannot ask', function (): void {
    $citoyen = Citoyen::factory()->create();
    ($this->deliverMail)($citoyen, 40);

    $this->withoutMockingConsoleOutput()
        ->artisan('email-management:citoyen-new-mail', ['--min-days' => 30, '--delete' => true, '--no-interaction' => true]);

    expect(Citoyen::query()->count())->toBe(1);
});

it('skips an account that forwards its mail', function (): void {
    $citoyen = Citoyen::factory()->create(['mail' => 'old@marche.be']);
    ($this->deliverMail)($citoyen, 40);
    ($this->writeSieve)($citoyen, 'redirect "ailleurs@example.be";');

    $this->artisan('email-management:citoyen-new-mail', ['--min-days' => 30, '--delete' => true])
        ->expectsOutputToContain('ailleurs@example.be')
        ->expectsOutputToContain('1 ignoré(s)')
        ->assertSuccessful();

    expect(Citoyen::query()->count())->toBe(1);
});

it('does not treat a commented redirect as a forward', function (): void {
    $citoyen = Citoyen::factory()->create(['mail' => 'old@marche.be']);
    ($this->deliverMail)($citoyen, 40);
    ($this->writeSieve)($citoyen, "# redirect \"ailleurs@example.be\";\n/* redirect \"autre@example.be\"; */\nkeep;");

    $this->artisan('email-management:citoyen-new-mail', ['--min-days' => 30, '--delete' => true])
        ->expectsConfirmation('Supprimer le compte old@marche.be ?', 'no')
        ->assertSuccessful();
});
