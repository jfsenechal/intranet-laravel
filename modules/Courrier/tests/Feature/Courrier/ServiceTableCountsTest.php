<?php

declare(strict_types=1);

use AcMarche\Courrier\Filament\Resources\Services\Pages\ListServices;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Service;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
    $this->admin = User::factory()->create(['is_administrator' => true]);
    $this->actingAs($this->admin);
});

test('the courriers column counts the attached incoming mails', function (): void {
    $withMails = Service::factory()->create();
    $withoutMails = Service::factory()->create();

    $withMails->incomingMails()->attach(
        IncomingMail::factory()->count(3)->create()->pluck('id')
    );

    livewire(ListServices::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$withMails, $withoutMails])
        ->assertTableColumnStateSet('incoming_mails_count', 3, $withMails)
        ->assertTableColumnStateSet('incoming_mails_count', 0, $withoutMails);
});
