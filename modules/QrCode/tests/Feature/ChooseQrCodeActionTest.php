<?php

declare(strict_types=1);

use AcMarche\QrCode\Enums\QrCodeActionEnum;
use AcMarche\QrCode\Filament\Pages\ChooseQrCodeAction;
use AcMarche\QrCode\Filament\Pages\GenerateQrCode;
use AcMarche\QrCode\Models\QrCode;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('qrcode-panel'));

    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('lists every action with its description and a link to the form', function (): void {
    $page = livewire(ChooseQrCodeAction::class)->assertSuccessful();

    foreach (QrCodeActionEnum::cases() as $action) {
        $page
            ->assertSee($action->getLabel())
            ->assertSee($action->getDescription())
            ->assertSee(GenerateQrCode::getUrl(['action' => $action->value]));
    }
});

it('preselects the action coming from the chooser', function (): void {
    $this->get(GenerateQrCode::getUrl(['action' => QrCodeActionEnum::WIFI->value]))
        ->assertSuccessful();

    Livewire::withQueryParams(['action' => QrCodeActionEnum::WIFI->value])
        ->test(GenerateQrCode::class)
        ->assertSuccessful()
        ->assertFormSet(['action' => QrCodeActionEnum::WIFI->value])
        ->assertFormFieldExists('dynamicTypeFields.ssid');
});

it('hides the action select when the action was chosen before the form', function (): void {
    Livewire::withQueryParams(['action' => QrCodeActionEnum::WIFI->value])
        ->test(GenerateQrCode::class)
        ->assertFormFieldExists('action', fn (Hidden $field): bool => true);
});

it('saves the generated QR code, hidden action included', function (): void {
    Livewire::withQueryParams(['action' => QrCodeActionEnum::WIFI->value])
        ->test(GenerateQrCode::class)
        ->fillForm([
            'name' => 'Wifi accueil',
            'ssid' => 'Marche',
            'encryption' => 'WPA',
            'password' => 'secret1234',
        ])
        ->call('generate')
        ->assertHasNoFormErrors()
        ->assertNotified();

    assertDatabaseHas(QrCode::class, [
        'name' => 'Wifi accueil',
        'action' => QrCodeActionEnum::WIFI->value,
        'ssid' => 'Marche',
        'user_id' => $this->user->id,
    ]);
});

it('updates the same record when generating again', function (): void {
    Livewire::withQueryParams(['action' => QrCodeActionEnum::URL->value])
        ->test(GenerateQrCode::class)
        ->fillForm(['name' => 'Site', 'message' => 'https://marche.be'])
        ->call('generate')
        ->fillForm(['name' => 'Site officiel', 'message' => 'https://marche.be'])
        ->call('generate')
        ->assertHasNoFormErrors();

    expect(QrCode::query()->count())->toBe(1);

    assertDatabaseHas(QrCode::class, ['name' => 'Site officiel']);
});

it('shows the action select when no action was chosen before', function (): void {
    livewire(GenerateQrCode::class)
        ->assertFormFieldExists('action', fn (Select $field): bool => true);
});

it('falls back to the url action when no action is given', function (): void {
    livewire(GenerateQrCode::class)
        ->assertSuccessful()
        ->assertFormSet(['action' => QrCodeActionEnum::URL]);
});
