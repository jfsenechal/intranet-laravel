<?php

declare(strict_types=1);

use AcMarche\Courrier\Filament\Resources\Services\Pages\ListServices;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
    $this->admin = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->admin);

    $this->empty = Service::factory()->create();

    $this->withRecipientOnly = Service::factory()->create();
    $this->withRecipientOnly->recipients()->attach(Recipient::factory()->create());

    $this->withMailOnly = Service::factory()->create();
    $this->withMailOnly->incomingMails()->attach(IncomingMail::factory()->create());

    $this->full = Service::factory()->create();
    $this->full->recipients()->attach(Recipient::factory()->create());
    $this->full->incomingMails()->attach(IncomingMail::factory()->create());
});

describe('without_recipients filter', function (): void {
    test('shows only the services holding no recipient', function (): void {
        livewire(ListServices::class)
            ->loadTable()
            ->filterTable('without_recipients', true)
            ->assertCanSeeTableRecords([$this->empty, $this->withMailOnly])
            ->assertCanNotSeeTableRecords([$this->withRecipientOnly, $this->full]);
    });

    test('shows only the services holding recipients when set to false', function (): void {
        livewire(ListServices::class)
            ->loadTable()
            ->filterTable('without_recipients', false)
            ->assertCanSeeTableRecords([$this->withRecipientOnly, $this->full])
            ->assertCanNotSeeTableRecords([$this->empty, $this->withMailOnly]);
    });
});

describe('without_incoming_mails filter', function (): void {
    test('shows only the services holding no courrier', function (): void {
        livewire(ListServices::class)
            ->loadTable()
            ->filterTable('without_incoming_mails', true)
            ->assertCanSeeTableRecords([$this->empty, $this->withRecipientOnly])
            ->assertCanNotSeeTableRecords([$this->withMailOnly, $this->full]);
    });
});

test('combining both filters isolates the fully empty services', function (): void {
    livewire(ListServices::class)
        ->loadTable()
        ->filterTable('without_recipients', true)
        ->filterTable('without_incoming_mails', true)
        ->assertCanSeeTableRecords([$this->empty])
        ->assertCanNotSeeTableRecords([$this->withRecipientOnly, $this->withMailOnly, $this->full]);
});

test('shows every service when no filter is applied', function (): void {
    livewire(ListServices::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$this->empty, $this->withRecipientOnly, $this->withMailOnly, $this->full]);
});
