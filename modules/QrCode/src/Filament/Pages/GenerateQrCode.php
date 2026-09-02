<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Filament\Pages;

use AcMarche\QrCode\Enums\QrCodeActionEnum;
use AcMarche\QrCode\Filament\Resources\QrCodes\Pages\ViewQrCode;
use AcMarche\QrCode\Filament\Resources\QrCodes\Schemas\QrCodeForm;
use AcMarche\QrCode\Models\QrCode;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;

final class GenerateQrCode extends Page implements HasForms
{
    use InteractsWithForms;

    /** @var array<string, mixed> */
    public array $data = [];

    /**
     * Kept as a Livewire property: the `action` query string is only present on the initial page
     * load, not on the subsequent Livewire update requests (live fields, generate).
     */
    public ?string $action = null;

    /**
     * True when the action came from ChooseQrCodeAction: the select is then replaced by a hidden
     * field, the choice having already been made on the previous page.
     */
    public bool $isActionLocked = false;

    protected string $view = 'qrcode::filament.pages.generate-qr-code';

    protected static ?string $title = 'Générer un QR code';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-qr-code';
    }

    public static function getNavigationLabel(): string
    {
        return 'Générer un QR code';
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function mount(): void
    {
        $chosenAction = QrCodeActionEnum::tryFrom((string) request()->query('action'));

        if ($this->action === null) {
            $this->action = $chosenAction?->value ?? QrCodeActionEnum::URL->value;
            $this->isActionLocked = $chosenAction instanceof QrCodeActionEnum;
        }

        $this->form->fill([
            'action' => $this->action,
            'color' => '#000000',
            'background_color' => '#FFFFFF',
            'format' => 'SVG',
            'style' => 'square',
            'pixels' => 400,
            'margin' => 10,
        ]);
    }

    public function getTitle(): string
    {
        $action = QrCodeActionEnum::tryFrom((string) $this->action);

        return $action instanceof QrCodeActionEnum
            ? 'Générer un QR code : '.mb_lcfirst($action->getLabel())
            : 'Générer un QR code';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components(
                QrCodeForm::configure($schema, isActionSelectable: ! $this->isActionLocked)->getComponents(),
            );
    }

    public function generate(): void
    {
        $qrCode = $this->makeModel($this->form->getState());
        $qrCode->user_id = auth()->id();
        $qrCode->username = auth()->user()?->username;
        $qrCode->save();

        Notification::make()
            ->success()
            ->title('QR code enregistré')
            ->body('Votre QR code a été ajouté à votre collection.')
            ->send();

        $this->redirect(ViewQrCode::getUrl(['record' => $qrCode]));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('changeAction')
                ->label('Changer d\'action')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('gray')
                ->url(ChooseQrCodeAction::getUrl()),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('generate')
                ->label('Générer')
                ->icon('heroicon-o-bolt')
                ->submit('generate'),
            Action::make('reset')
                ->label('Réinitialiser')
                ->color('gray')
                ->action(function (): void {
                    $this->mount();
                }),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function makeModel(array $data): QrCode
    {
        $qrCode = new QrCode();
        $qrCode->fill($data);
        $qrCode->name = $data['name'] ?? 'QR code';
        $qrCode->action = $data['action'] instanceof QrCodeActionEnum
            ? $data['action']
            : QrCodeActionEnum::from((string) $data['action']);

        return $qrCode;
    }
}
