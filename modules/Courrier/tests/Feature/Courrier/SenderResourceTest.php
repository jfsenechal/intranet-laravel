<?php

declare(strict_types=1);

use AcMarche\Courrier\Filament\Resources\Senders\Pages\CreateSender;
use AcMarche\Courrier\Filament\Resources\Senders\Pages\EditSender;
use AcMarche\Courrier\Filament\Resources\Senders\Pages\ListSenders;
use AcMarche\Courrier\Filament\Resources\Senders\Pages\ViewSender;
use AcMarche\Courrier\Models\Sender;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
    $this->admin = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->admin);
});

describe('Sender Resource', function (): void {
    test('can list senders', function (): void {
        $senders = Sender::factory()->count(3)->create();

        livewire(ListSenders::class)
            ->loadTable()
            ->assertCanSeeTableRecords($senders);
    });

    test('can create a sender', function (): void {
        livewire(CreateSender::class)
            ->fillForm([
                'name' => 'Test Expéditeur',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        assertDatabaseHas(Sender::class, [
            'name' => 'Test Expéditeur',
        ]);
    });

    test('can view a sender', function (): void {
        $sender = Sender::factory()->create();

        livewire(ViewSender::class, ['record' => $sender->getRouteKey()])
            ->assertSuccessful();
    });

    test('can edit a sender', function (): void {
        $sender = Sender::factory()->create();

        livewire(EditSender::class, ['record' => $sender->getRouteKey()])
            ->fillForm([
                'name' => 'Nom Modifié',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        assertDatabaseHas(Sender::class, [
            'id' => $sender->id,
            'name' => 'Nom Modifié',
        ]);
    });

    test('name is required', function (): void {
        livewire(CreateSender::class)
            ->fillForm([
                'name' => null,
            ])
            ->call('create')
            ->assertHasFormErrors([
                'name' => 'required',
            ]);
    });
});
