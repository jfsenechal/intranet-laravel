<?php

declare(strict_types=1);

use AcMarche\Courrier\Filament\Resources\Recipients\Pages\ListRecipients;
use AcMarche\Courrier\Models\Recipient;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
    $this->admin = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->admin);
});

describe('receives_attachments filter', function (): void {
    test('shows only recipients receiving attachments when set to true', function (): void {
        $withAttachments = Recipient::factory()->receivesAttachments()->create();
        $withoutAttachments = Recipient::factory()->create(['receives_attachments' => false]);

        livewire(ListRecipients::class)
            ->loadTable()
            ->filterTable('receives_attachments', true)
            ->assertCanSeeTableRecords([$withAttachments])
            ->assertCanNotSeeTableRecords([$withoutAttachments]);
    });

    test('shows only recipients not receiving attachments when set to false', function (): void {
        $withAttachments = Recipient::factory()->receivesAttachments()->create();
        $withoutAttachments = Recipient::factory()->create(['receives_attachments' => false]);

        livewire(ListRecipients::class)
            ->loadTable()
            ->filterTable('receives_attachments', false)
            ->assertCanSeeTableRecords([$withoutAttachments])
            ->assertCanNotSeeTableRecords([$withAttachments]);
    });

    test('shows all recipients when the filter is not applied', function (): void {
        $withAttachments = Recipient::factory()->receivesAttachments()->create();
        $withoutAttachments = Recipient::factory()->create(['receives_attachments' => false]);

        livewire(ListRecipients::class)
            ->loadTable()
            ->assertCanSeeTableRecords([$withAttachments, $withoutAttachments]);
    });
});
