<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\ViewIncomingMail;
use AcMarche\Courrier\Models\IncomingMail;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

it('shows the department in the metadata section', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $mail = IncomingMail::factory()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
    ]);

    livewire(ViewIncomingMail::class, ['record' => $mail->id])
        ->assertSee('Département')
        ->assertSee(DepartmentCourrierEnum::VILLE->value);
});
