<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Pages\Inbox;
use AcMarche\Security\Models\Role;
use App\Models\User;
use DirectoryTree\ImapEngine\Laravel\ImapManager;
use DirectoryTree\ImapEngine\Testing\FakeFolder;
use DirectoryTree\ImapEngine\Testing\FakeMailbox;
use DirectoryTree\ImapEngine\Testing\FakeMessage;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

/**
 * Build a raw MIME message carrying a single attachment.
 */
function fakeMailWithAttachment(int $uid): FakeMessage
{
    $mime = 'Date: '.now()->toRfc2822String()."\r\n"
        ."From: Jean Test <jean@example.com>\r\n"
        ."Subject: Sujet test\r\n"
        ."MIME-Version: 1.0\r\n"
        ."Content-Type: multipart/mixed; boundary=\"BOUND\"\r\n"
        ."\r\n"
        ."--BOUND\r\n"
        ."Content-Type: text/plain; charset=UTF-8\r\n\r\n"
        ."Corps du message\r\n"
        ."--BOUND\r\n"
        ."Content-Type: application/pdf; name=\"doc.pdf\"\r\n"
        ."Content-Disposition: attachment; filename=\"doc.pdf\"\r\n"
        ."Content-Transfer-Encoding: base64\r\n\r\n"
        .base64_encode('%PDF-1.4 fake')."\r\n"
        .'--BOUND--'."\r\n";

    return new FakeMessage(uid: $uid, contents: $mime);
}

it('mounts the process action for a mail with a single attachment', function (): void {
    $fakeMailbox = new FakeMailbox(folders: [
        new FakeFolder('inbox', messages: [fakeMailWithAttachment(1)]),
    ]);

    resolve(ImapManager::class)->swap('imap_cpas', $fakeMailbox);

    $user = User::factory()->create();
    $user->addRole(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]));
    $this->actingAs($user);

    livewire(Inbox::class)
        ->mountAction(TestAction::make('process')->table('0'))
        ->assertActionMounted(TestAction::make('process')->table('0'))
        ->assertHasNoActionErrors();
});

it('pre-fills the next cpas reference number on the process action', function (): void {
    $fakeMailbox = new FakeMailbox(folders: [
        new FakeFolder('inbox', messages: [fakeMailWithAttachment(1)]),
    ]);

    resolve(ImapManager::class)->swap('imap_cpas', $fakeMailbox);

    AcMarche\Courrier\Models\IncomingMail::factory()->create([
        'department' => AcMarche\Courrier\Enums\DepartmentCourrierEnum::CPAS->value,
    ]);

    $user = User::factory()->create();
    $user->addRole(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]));
    $this->actingAs($user);

    livewire(Inbox::class)
        ->mountAction(TestAction::make('process')->table('0'))
        ->assertActionDataSet(['reference_number' => '2']);
});
