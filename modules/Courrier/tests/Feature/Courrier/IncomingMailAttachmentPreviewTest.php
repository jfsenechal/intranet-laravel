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
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

function createMailWithStoredAttachment(string $department, bool $storeFile = true): IncomingMail
{
    $mail = IncomingMail::factory()->create(['department' => $department]);
    $path = 'courrier/'.mb_strtolower($department).'/'.$mail->id.'/doc.pdf';

    $disk = Storage::fake(config('courrier.storage.disk'));

    if ($storeFile) {
        $disk->put($path, 'PDF-CONTENT');
    }

    Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'doc.pdf',
        'mime' => 'application/pdf',
        'path' => $path,
    ]);

    return $mail;
}

it('embeds the attachment preview for a user who can download it', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $mail = createMailWithStoredAttachment(DepartmentCourrierEnum::VILLE->value);
    $attachment = $mail->attachments->first();

    livewire(ViewIncomingMail::class, ['record' => $mail->id])
        ->assertSee('Pièce jointe')
        ->assertSee(route('courrier.attachments.preview-stored', $attachment))
        ->assertSee('Ouvrir dans un nouvel onglet');
});

it('hides the attachment preview from an index user who cannot download it', function (): void {
    $user = User::factory()->create();
    $user->roles()->attach(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]));
    $this->actingAs($user);

    $mail = createMailWithStoredAttachment(DepartmentCourrierEnum::VILLE->value);
    $attachment = $mail->attachments->first();

    livewire(ViewIncomingMail::class, ['record' => $mail->id])
        ->assertDontSee(route('courrier.attachments.preview-stored', $attachment));
});

it('hides the attachment preview when the stored file is missing', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $mail = createMailWithStoredAttachment(DepartmentCourrierEnum::VILLE->value, storeFile: false);
    $attachment = $mail->attachments->first();

    livewire(ViewIncomingMail::class, ['record' => $mail->id])
        ->assertDontSee(route('courrier.attachments.preview-stored', $attachment));
});
