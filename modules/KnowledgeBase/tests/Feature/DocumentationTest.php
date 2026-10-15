<?php

declare(strict_types=1);

use AcMarche\KnowledgeBase\Documentation\Documentation;
use AcMarche\KnowledgeBase\Documentation\SecretScanner;
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
    'navigation file' => 'documentation',
    'create route' => 'create',
    'navigation route' => 'navigation',
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

it('builds the sidebar from documentation.md and drops missing or invalid pages', function (): void {
    $this->disk->put('vpn-forticlient.md', '# VPN');
    $this->disk->put('git-workflow.md', '# Git');
    $this->disk->put('documentation.md', <<<'MD'
        - ## Réseau
            - [VPN FortiClient](vpn-forticlient)
            - [Page supprimée](deleted-page)
            - [Évasion](../secret)
        - ## Développement
            - [Workflow Git](/git-workflow)
        MD);

    expect($this->documentation->navigation())->toBe([
        ['section' => 'Réseau', 'pages' => [['slug' => 'vpn-forticlient', 'title' => 'VPN FortiClient']]],
        ['section' => 'Développement', 'pages' => [['slug' => 'git-workflow', 'title' => 'Workflow Git']]],
    ]);
});

it('lists pages with their section and reports unlisted ones', function (): void {
    $this->disk->put('vpn-forticlient.md', '# VPN');
    $this->disk->put('orphan.md', '# Orphan');
    $this->disk->put('documentation.md', "- ## Réseau\n    - [VPN](vpn-forticlient)\n");

    $documents = $this->documentation->all();

    expect($documents->keys()->all())->toBe(['orphan', 'vpn-forticlient'])
        ->and($documents['vpn-forticlient']['section'])->toBe('Réseau')
        ->and($documents['orphan']['section'])->toBeNull()
        ->and($this->documentation->unlistedSlugs())->toBe(['orphan']);
});

it('escapes raw html and drops unsafe links when rendering', function (): void {
    $html = $this->documentation->toHtml("<script>alert('x')</script>\n\n[click](javascript:alert(1))");

    expect($html)->toContain('&lt;script&gt;')
        ->not->toContain('<script>')
        ->not->toContain('javascript:');
});

it('highlights fenced code blocks with light and dark themes', function (): void {
    $html = $this->documentation->toHtml("```php\n<?php echo 'kb';\n```");

    expect($html)->toContain('class="phiki')
        ->toContain('--phiki-dark-')
        ->not->toContain("<?php echo 'kb';");
});

it('renders a fenced block without language as escaped code', function (): void {
    $html = $this->documentation->toHtml("```\n<b>raw</b>\n```");

    expect($html)->toContain('&lt;b&gt;raw&lt;/b&gt;')
        ->not->toContain('<b>raw</b>');
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
