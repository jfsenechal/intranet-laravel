<?php

declare(strict_types=1);

use AcMarche\App\Enums\SignatureEnum;
use AcMarche\App\Filament\Resources\Signatures\Pages\ViewSignature;
use AcMarche\App\Models\Signature;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('app-panel'));
    $this->user = User::factory()->create(['username' => 'cboldo']);
    $this->actingAs($this->user);

    $this->signature = Signature::create([
        'username' => 'cboldo',
        'first_name' => 'Catherine',
        'last_name' => 'Boldo',
        'address' => 'Boulevard du Midi 20',
        'postal_code' => '6900',
        'city' => 'Marche-en-Famenne',
        'email' => 'catherine.boldo@cpas.marche.be',
        'logo' => SignatureEnum::CPAS,
    ]);
});

it('downloads the signature as an html file', function (): void {
    Livewire::test(ViewSignature::class, ['record' => $this->signature->id])
        ->callAction(TestAction::make('download'))
        ->assertFileDownloaded('signature-'.$this->signature->id.'.html');
});

it('copies the signature to the clipboard and notifies instead of opening a modal', function (): void {
    $html = Livewire::test(ViewSignature::class, ['record' => $this->signature->id])
        ->assertActionExists('copy')
        ->html();

    expect($html)
        ->toContain('clipboard.writeText')
        ->toContain('FilamentNotification');
});
