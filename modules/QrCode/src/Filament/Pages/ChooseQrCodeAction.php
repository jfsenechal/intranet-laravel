<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Filament\Pages;

use AcMarche\QrCode\Enums\QrCodeActionEnum;
use Filament\Pages\Page;

final class ChooseQrCodeAction extends Page
{
    protected string $view = 'qrcode::filament.pages.choose-qr-code-action';

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

    public function getSubheading(): string
    {
        return 'Choisissez ce que doit faire le téléphone qui scannera votre QR code.';
    }

    /**
     * @return array<int, QrCodeActionEnum>
     */
    public function getQrCodeActions(): array
    {
        return QrCodeActionEnum::cases();
    }

    public function getActionUrl(QrCodeActionEnum $action): string
    {
        return GenerateQrCode::getUrl(['action' => $action->value]);
    }
}
