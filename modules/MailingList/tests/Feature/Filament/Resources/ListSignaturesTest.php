<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\Signatures\Pages\CreateSignature;
use AcMarche\MailingList\Filament\Resources\Signatures\Pages\ListSignatures;
use AcMarche\MailingList\Filament\Resources\Signatures\SignatureResource;
use AcMarche\MailingList\Models\Signature;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseCount;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list-panel'));
    $this->user = User::factory()->create(['username' => 'cboldo', 'phone' => null, 'mobile' => null]);
    $this->actingAs($this->user);
});

function createSignatureFor(string $username, string $service): Signature
{
    return Signature::create([
        'username' => $username,
        'first_name' => 'Catherine',
        'last_name' => 'Boldo',
        'address' => 'Boulevard du Midi 20',
        'postal_code' => '6900',
        'city' => 'Marche-en-Famenne',
        'email' => 'catherine.boldo@cpas.marche.be',
        'service' => $service,
    ]);
}

it('redirects to the create page when the user has no signature', function (): void {
    Livewire::test(ListSignatures::class)
        ->assertRedirect(SignatureResource::getUrl('create'));
});

it('lists every signature of the user but not those of others', function (): void {
    $crèche = createSignatureFor('cboldo', 'Crèche');
    $abri = createSignatureFor('cboldo', "Maison G'Abri'Elles");
    $other = createSignatureFor('jdoe', 'Travaux');

    Livewire::test(ListSignatures::class)
        ->assertNoRedirect()
        ->loadTable()
        ->assertCanSeeTableRecords([$crèche, $abri])
        ->assertCanNotSeeTableRecords([$other]);
});

it('lets a user create another signature when one already exists', function (): void {
    createSignatureFor('cboldo', 'Crèche');

    Livewire::test(CreateSignature::class)
        ->assertOk()
        ->fillForm([
            'first_name' => 'Catherine',
            'last_name' => 'Boldo',
            'address' => 'Boulevard du Midi 20',
            'postal_code' => '6900',
            'city' => 'Marche-en-Famenne',
            'email' => 'catherine.boldo@cpas.marche.be',
            'service' => "Maison G'Abri'Elles",
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseCount(Signature::class, 2);
});
