<?php

declare(strict_types=1);

use AcMarche\Agent\Documentation\Documentation;
use AcMarche\Agent\Documentation\SecretScanner;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('knowledge-base');
    $this->disk = Storage::disk('knowledge-base');
    $this->documentation = new Documentation($this->disk);
});

it('accepts a lowercase dashed slug', function (): void {
    expect($this->documentation->isValidSlug('vpn-forticlient'))->toBeTrue();
});

it('rejects slugs that are malformed, reserved or traverse paths', function (string $slug): void {
    expect($this->documentation->isValidSlug($slug))->toBeFalse();
})->with([
    'parent path' => '../etc/passwd',
    'uppercase' => 'Vpn',
    'double dash' => 'a--b',
    'empty' => '',
    'create route' => 'create',
    'too long' => str_repeat('a', 101),
]);

it('never reads a file outside the slug rule', function (): void {
    $this->disk->put('secret.txt', 'top secret');

    expect(fn () => $this->documentation->get('../secret'))->toThrow(InvalidArgumentException::class);
});

it('takes the title from the first level one heading', function (): void {
    $this->disk->put('vpn.md', "Intro\n# VPN FortiClient\n## Installation\n");
    $this->disk->put('git-workflow.md', "No heading here\n");

    expect($this->documentation->title('vpn'))->toBe('VPN FortiClient')
        ->and($this->documentation->title('git-workflow'))->toBe('Git Workflow');
});

it('normalises line endings and the trailing newline when saving', function (): void {
    $this->documentation->put('vpn', "# VPN\r\nline\r\n\r\n\r\n");

    expect($this->disk->get('vpn.md'))->toBe("# VPN\nline\n");
});

it('lists every markdown page sorted by title and ignores other files', function (): void {
    $this->disk->put('vpn-forticlient.md', '# VPN');
    $this->disk->put('backup-restore.md', '# Sauvegarde');
    $this->disk->put('notes.txt', 'not a page');
    $this->disk->put('images/a.md', '# Not at the root');

    expect($this->documentation->all()->keys()->all())->toBe(['backup-restore', 'vpn-forticlient']);
});

it('flags lines that look like secrets', function (string $line, string $rule): void {
    expect((new SecretScanner)->findings("intro\n{$line}"))->toBe([['line' => 2, 'rule' => $rule]]);
})->with([
    'password' => ['password: Hunter2xyz', 'password'],
    'mot de passe' => ['mot de passe = Azerty123', 'password'],
    'private key' => ['-----BEGIN RSA PRIVATE KEY-----', 'private-key'],
    'aws key' => ['AKIAABCDEFGHIJKLMNOP', 'aws-key'],
    'api token' => ['api_key=abcdef0123456789xyz', 'token'],
]);

it('does not flag prose that mentions the vault', function (): void {
    expect((new SecretScanner)->findings('Le mot de passe est dans le coffre-fort.'))->toBe([]);
});
