<?php

declare(strict_types=1);

use AcMarche\App\Filament\Resources\Signatures\Pages\CreateSignature;
use AcMarche\App\Models\Signature;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('app-panel'));
    $this->user = User::factory()->create([
        'first_name' => 'Catherine',
        'last_name' => 'Boldo',
        'username' => 'cboldo',
        'email' => 'catherine.boldo@cpas.marche.be',
        'phone' => '084000000',
        'mobile' => '0492266578',
    ]);
    $this->actingAs($this->user);
});

it('creates a signature and stores the authenticated username', function (): void {
    Livewire::test(CreateSignature::class)
        ->fillForm([
            'first_name' => 'Catherine',
            'last_name' => 'Boldo',
            'job_title' => 'Puéricultrice',
            'service' => "Maison G'Abri'Elles",
            'address' => 'Boulevard du Midi 20',
            'postal_code' => '6900',
            'city' => 'Marche-en-Famenne',
            'email' => 'catherine.boldo@cpas.marche.be',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(Signature::class, [
        'username' => 'cboldo',
        'first_name' => 'Catherine',
        'last_name' => 'Boldo',
        'email' => 'catherine.boldo@cpas.marche.be',
    ]);
});

it('requires the mandatory fields before creating a signature', function (): void {
    Livewire::test(CreateSignature::class)
        ->fillForm([
            'first_name' => null,
            'last_name' => null,
            'address' => null,
            'email' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'first_name' => 'required',
            'last_name' => 'required',
            'address' => 'required',
            'email' => 'required',
        ]);
});
