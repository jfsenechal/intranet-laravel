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
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

/**
 * Build a minimal raw MIME message with the given subject.
 */
function fakeMail(int $uid, string $subject): FakeMessage
{
    $mime = 'Date: '.now()->toRfc2822String()."\r\n"
        ."From: Jean Test <jean@example.com>\r\n"
        ."Subject: {$subject}\r\n"
        ."MIME-Version: 1.0\r\n"
        ."Content-Type: text/plain; charset=UTF-8\r\n\r\n"
        ."Corps du message\r\n";

    return new FakeMessage(uid: $uid, contents: $mime);
}

it('refreshes the inbox list after deleting a message', function (): void {
    $fakeMailbox = new FakeMailbox(folders: [
        new FakeFolder('inbox', messages: [
            fakeMail(1, 'Premier message'),
            fakeMail(2, 'Second message'),
        ]),
    ]);

    resolve(ImapManager::class)->swap('imap_cpas', $fakeMailbox);

    $user = User::factory()->create();
    $user->addRole(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]));
    $this->actingAs($user);

    livewire(Inbox::class)
        ->call('loadTable')
        ->assertSee('Premier message')
        ->assertSee('Second message')
        ->callTableBulkAction('delete', ['0'])
        ->assertNotified('Messages supprimés')
        ->assertDontSee('Premier message')
        ->assertSee('Second message');
});
