<?php

declare(strict_types=1);

use AcMarche\App\Enums\SignatureEnum;
use AcMarche\App\Filament\Resources\Signatures\Pages\EditSignature;
use AcMarche\App\Models\Signature;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
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

it('opens a signature whose stored logo was renamed out of the enum', function (): void {
    DB::table('signatures')
        ->where('id', $this->signature->getKey())
        ->update(['logo' => 'mtfa.png']);

    Livewire::test(EditSignature::class, ['record' => $this->signature->getKey()])
        ->assertOk()
        ->assertFormSet(['logo' => SignatureEnum::MARCHE->value]);
});

it('saves the fallback logo once the signature is edited', function (): void {
    DB::table('signatures')
        ->where('id', $this->signature->getKey())
        ->update(['logo' => 'mtfa.png']);

    Livewire::test(EditSignature::class, ['record' => $this->signature->getKey()])
        ->fillForm(['job_title' => 'Puéricultrice'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($this->signature->fresh()->logo)->toBe(SignatureEnum::MARCHE);
});
