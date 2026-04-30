<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Filament\Resources\QrCodes\Pages;

use AcMarche\QrCode\Filament\Resources\QrCodes\QrCodeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditQrCode extends EditRecord
{
    #[Override]
    protected static string $resource = QrCodeResource::class;

    public function getTitle(): string
    {
        return 'Modifier le QR code';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->icon('tabler-eye'),
            DeleteAction::make()->icon('tabler-trash'),
        ];
    }
}
