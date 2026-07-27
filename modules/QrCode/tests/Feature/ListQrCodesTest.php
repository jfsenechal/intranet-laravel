<?php

declare(strict_types=1);

use AcMarche\QrCode\Filament\Resources\QrCodes\Pages\ListQrCodes;
use AcMarche\QrCode\Models\QrCode;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('qrcode-panel'));

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('lists the QR codes of the current user', function (): void {
    $mine = QrCode::factory()->create(['user_id' => $this->user->id]);

    livewire(ListQrCodes::class)
        ->assertSuccessful()
        ->loadTable()
        ->assertCanSeeTableRecords([$mine]);
});

it('hides the QR codes of the other users', function (): void {
    $mine = QrCode::factory()->create(['user_id' => $this->user->id]);
    $theirs = QrCode::factory()->create();

    livewire(ListQrCodes::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$mine])
        ->assertCanNotSeeTableRecords([$theirs]);
});

it('hides a soft deleted QR code', function (): void {
    $deleted = QrCode::factory()->create(['user_id' => $this->user->id]);
    $deleted->delete();

    livewire(ListQrCodes::class)
        ->assertSuccessful()
        ->loadTable()
        ->assertCanNotSeeTableRecords([$deleted]);
});
