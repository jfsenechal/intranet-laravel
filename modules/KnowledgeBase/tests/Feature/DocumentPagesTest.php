<?php

declare(strict_types=1);

use AcMarche\KnowledgeBase\Filament\Pages\CreateDocument;
use AcMarche\KnowledgeBase\Filament\Pages\EditDocument;
use AcMarche\KnowledgeBase\Filament\Pages\EditNavigation;
use AcMarche\KnowledgeBase\Filament\Pages\ListDocuments;
use AcMarche\KnowledgeBase\Filament\Pages\ShowDocument;
use AcMarche\KnowledgeBase\Tests\KnowledgeBaseUsers;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('knowledge-base-panel'));
    Storage::fake('knowledge-base');
    $this->disk = Storage::disk('knowledge-base');
    $this->disk->put('vpn-forticlient.md', "# VPN FortiClient\n\nInstaller le client.\n");
    $this->disk->put('git-workflow.md', "# Workflow Git\n\nBranches et revues.\n");
    $this->actingAs(KnowledgeBaseUsers::make('editor'));
});

it('lists the pages and searches their content', function (): void {
    livewire(ListDocuments::class)
        ->loadTable()
        ->assertCanSeeTableRecords(['vpn-forticlient', 'git-workflow'])
        ->searchTable('revues')
        ->assertCanSeeTableRecords(['git-workflow'])
        ->assertCanNotSeeTableRecords(['vpn-forticlient']);
});

it('shows a page with its rendered content and sidebar', function (): void {
    $this->disk->put('documentation.md', "- ## Réseau\n    - [VPN FortiClient](vpn-forticlient)\n");

    livewire(ShowDocument::class, ['document' => 'vpn-forticlient'])
        ->assertOk()
        ->assertSee('<h1>VPN FortiClient</h1>', escape: false)
        ->assertSee('Réseau');
});

it('returns not found for an unknown or invalid page', function (string $slug): void {
    livewire(ShowDocument::class, ['document' => $slug])->assertNotFound();
})->with(['unknown-page', 'Bad Slug', 'documentation']);

it('creates a page and reopens it through its slug', function (): void {
    livewire(CreateDocument::class)
        ->fillForm(['slug' => 'deploy-laravel-app', 'content' => "# Déployer\r\n\r\nÉtapes.\r\n"])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect(ShowDocument::getUrl(['document' => 'deploy-laravel-app']));

    expect($this->disk->get('deploy-laravel-app.md'))->toBe("# Déployer\n\nÉtapes.\n");

    livewire(ShowDocument::class, ['document' => 'deploy-laravel-app'])
        ->assertOk()
        ->assertSee('Déployer');
});

it('rejects an invalid, reserved or existing slug without writing', function (string $slug, string $message): void {
    livewire(CreateDocument::class)
        ->fillForm(['slug' => $slug, 'content' => '# Nouveau'])
        ->call('save')
        ->assertHasFormErrors(['slug'])
        ->assertSee($message);

    expect($this->disk->allFiles())->toEqualCanonicalizing(['vpn-forticlient.md', 'git-workflow.md']);
})->with([
    'existing' => ['vpn-forticlient', 'Une page avec cet identifiant existe déjà.'],
    'malformed' => ['Bad Slug', 'Utilisez uniquement des minuscules, des chiffres et des tirets simples.'],
    'reserved' => ['documentation', 'Cet identifiant est réservé.'],
]);

it('saves an edited page', function (): void {
    livewire(EditDocument::class, ['document' => 'vpn-forticlient'])
        ->assertSchemaStateSet(['content' => "# VPN FortiClient\n\nInstaller le client.\n"])
        ->fillForm(['content' => "# VPN FortiClient\n\nVersion 7.4.\n"])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect(ShowDocument::getUrl(['document' => 'vpn-forticlient']));

    expect($this->disk->get('vpn-forticlient.md'))->toBe("# VPN FortiClient\n\nVersion 7.4.\n");
});

it('refuses to overwrite a page changed by someone else since it was opened', function (): void {
    $component = livewire(EditDocument::class, ['document' => 'vpn-forticlient']);

    $this->disk->put('vpn-forticlient.md', "# VPN\n\nChangement d'un collègue.\n");

    $component
        ->fillForm(['content' => "# VPN\n\nMa version.\n"])
        ->call('save')
        ->assertNotified('Modification concurrente')
        ->assertNoRedirect();

    expect($this->disk->get('vpn-forticlient.md'))->toBe("# VPN\n\nChangement d'un collègue.\n");
});

it('saves content that looks like a secret but warns the editor', function (): void {
    livewire(EditDocument::class, ['document' => 'vpn-forticlient'])
        ->fillForm(['content' => "# VPN\n\npassword: Hunter2xyz\n"])
        ->call('save')
        ->assertNotified('Possible secret détecté');

    expect($this->disk->get('vpn-forticlient.md'))->toContain('password: Hunter2xyz');
});

it('deletes a page', function (): void {
    livewire(ShowDocument::class, ['document' => 'vpn-forticlient'])
        ->callAction('delete')
        ->assertRedirect(ListDocuments::getUrl());

    expect($this->disk->exists('vpn-forticlient.md'))->toBeFalse();
});

it('saves the sidebar index', function (): void {
    livewire(EditNavigation::class)
        ->fillForm(['source' => "- ## Réseau\n    - [VPN](vpn-forticlient)"])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertRedirect(ListDocuments::getUrl());

    expect($this->disk->get('documentation.md'))->toBe("- ## Réseau\n    - [VPN](vpn-forticlient)\n");
});
