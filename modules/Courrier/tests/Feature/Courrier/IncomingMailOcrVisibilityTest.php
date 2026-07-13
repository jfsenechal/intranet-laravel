<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\ViewIncomingMail;
use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

function createMailWithOcr(string $department): IncomingMail
{
    $mail = IncomingMail::factory()->create(['department' => $department]);

    Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'doc.pdf',
        'mime' => 'application/pdf',
    ]);

    // Set the OCR content after creation: the create hook runs the indexing job
    // (which resets content), and there is no indexing hook on update.
    $mail->update(['content' => 'Texte OCR extrait']);

    return $mail;
}

it('shows the OCR content to a user who can download the attachment', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $mail = createMailWithOcr(DepartmentCourrierEnum::VILLE->value);

    livewire(ViewIncomingMail::class, ['record' => $mail->id])
        ->assertSee('Contenu (OCR)')
        ->assertSee('Texte OCR extrait');
});

it('hides the OCR content from an index user who cannot download the attachment', function (): void {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]);
    $user->roles()->attach($role);
    $this->actingAs($user);

    $mail = createMailWithOcr(DepartmentCourrierEnum::VILLE->value);

    livewire(ViewIncomingMail::class, ['record' => $mail->id])
        ->assertDontSee('Contenu (OCR)')
        ->assertDontSee('Texte OCR extrait');
});
