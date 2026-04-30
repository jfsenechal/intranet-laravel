<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Filament\Resources\QrCodes\Pages;

use AcMarche\QrCode\Filament\Resources\QrCodes\QrCodeResource;
use AcMarche\QrCode\Models\QrCode;
use AcMarche\QrCode\Service\QrCodeGenerator;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;
use Override;

final class ViewQrCode extends ViewRecord
{
    #[Override]
    protected static string $resource = QrCodeResource::class;

    public function getTitle(): string
    {
        return 'QR Code : '.$this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Télécharger')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function (): \Symfony\Component\HttpFoundation\StreamedResponse {
                    /** @var QrCode $record */
                    $record = $this->record;
                    $generator = app(QrCodeGenerator::class);
                    $content = $generator->render($record);
                    $filename = Str::slug($record->name ?? 'qrcode').'.'.$generator->extension($record);

                    return response()->streamDownload(
                        fn () => print $content,
                        $filename,
                        ['Content-Type' => $generator->mimeType($record)],
                    );
                }),
            EditAction::make()->icon('tabler-edit'),
            DeleteAction::make()->icon('tabler-trash'),
        ];
    }
}
