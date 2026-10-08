<?php

declare(strict_types=1);

use AcMarche\MailingList\Filament\Resources\Emails\Pages\CreateEmail;
use AcMarche\MailingList\Filament\Resources\Senders\SenderResource;
use AcMarche\MailingList\Models\Sender;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Filament::setCurrentPanel(Filament::getPanel('mailing-list-panel'));
});

it('redirects to sender creation when the user has no sender', function () {
    Sender::factory()->create([
        'username' => User::factory()->create()->username,
    ]);

    livewire(CreateEmail::class)
        ->assertRedirect(SenderResource::getUrl('create'))
        ->assertNotified(
            Notification::make()
                ->warning()
                ->title('Aucun expéditeur')
                ->body('Vous devez créer un expéditeur avant de pouvoir créer une campagne.')
                ->persistent()
        );
});

it('renders the create page when the user has a sender', function () {
    Sender::factory()->create([
        'username' => auth()->user()->username,
    ]);

    livewire(CreateEmail::class)
        ->assertOk()
        ->assertNoRedirect();
});
