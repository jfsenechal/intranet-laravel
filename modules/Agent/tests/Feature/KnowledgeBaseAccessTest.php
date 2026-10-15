<?php

declare(strict_types=1);

use AcMarche\Agent\Filament\Pages\CreateDocument;
use AcMarche\Agent\Filament\Pages\EditDocument;
use AcMarche\Agent\Filament\Pages\ListDocuments;
use AcMarche\Agent\Filament\Pages\ShowDocument;
use AcMarche\Agent\KnowledgeBaseAccess;
use AcMarche\Agent\Tests\KnowledgeBaseUsers;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('knowledge-base-panel'));
    Storage::fake('knowledge-base');
    Storage::disk('knowledge-base')->put('vpn-forticlient.md', "# VPN FortiClient\n");
});

it('grants read and write according to the user profile', function (string $profile, bool $canRead, bool $canWrite): void {
    $user = KnowledgeBaseUsers::make($profile);

    expect(KnowledgeBaseAccess::canRead($user))->toBe($canRead)
        ->and(KnowledgeBaseAccess::canWrite($user))->toBe($canWrite);
})->with([
    'guest' => ['guest', false, false],
    'user without role' => ['no role', false, false],
    'reader' => ['reader', true, false],
    'editor' => ['editor', true, true],
    'is_administrator' => ['administrator', true, true],
    'ROLE_INTRANET_ADMIN' => ['intranet admin role', true, true],
]);

it('forbids reading pages to a user without a knowledge base role', function (): void {
    $this->actingAs(KnowledgeBaseUsers::make('no role'));

    livewire(ListDocuments::class)->assertForbidden();
    livewire(ShowDocument::class, ['document' => 'vpn-forticlient'])->assertForbidden();
});

it('forbids writing pages to a reader and hides the write actions', function (): void {
    $this->actingAs(KnowledgeBaseUsers::make('reader'));

    livewire(ShowDocument::class, ['document' => 'vpn-forticlient'])
        ->assertOk()
        ->assertActionHidden('edit')
        ->assertActionHidden('delete');
    livewire(ListDocuments::class)
        ->assertOk()
        ->assertActionHidden('create');
    livewire(CreateDocument::class)->assertForbidden();
    livewire(EditDocument::class, ['document' => 'vpn-forticlient'])->assertForbidden();
});

it('lets an editor and an intranet administrator open every page', function (string $profile): void {
    $this->actingAs(KnowledgeBaseUsers::make($profile));

    livewire(ListDocuments::class)->assertOk()->assertActionVisible('create');
    livewire(ShowDocument::class, ['document' => 'vpn-forticlient'])->assertOk()->assertActionVisible('edit');
    livewire(CreateDocument::class)->assertOk();
    livewire(EditDocument::class, ['document' => 'vpn-forticlient'])->assertOk();
})->with(['editor', 'administrator', 'intranet admin role']);
