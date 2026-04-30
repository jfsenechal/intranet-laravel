<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Filament\Resources\QrCodes\Pages;

use AcMarche\QrCode\Filament\Pages\GenerateQrCode;
use AcMarche\QrCode\Filament\Resources\QrCodes\QrCodeResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListQrCodes extends ListRecords
{
    #[Override]
    protected static string $resource = QrCodeResource::class;

    public function getTitle(): string
    {
        return 'Mes QR codes';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('generate')
                ->label('Générer un QR code')
                ->icon('heroicon-o-plus')
                ->url(GenerateQrCode::getUrl()),
        ];
    }
}
