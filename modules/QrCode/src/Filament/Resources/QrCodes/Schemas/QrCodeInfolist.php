<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Filament\Resources\QrCodes\Schemas;

use AcMarche\QrCode\Models\QrCode;
use AcMarche\QrCode\Service\QrCodeGenerator;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;

final class QrCodeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->schema([
                Section::make('Aperçu')
                    ->columnSpan(1)
                    ->schema([
                        View::make('qrcode::filament.qrcode-preview')
                            ->viewData([
                                'getPreview' => fn (QrCode $record): string => self::buildPreview($record),
                            ]),
                    ]),

                Section::make('Détails')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nom'),
                        TextEntry::make('action')->label('Action')->badge(),
                        TextEntry::make('format')->label('Format'),
                        TextEntry::make('pixels')->label('Taille')->suffix(' px'),
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('user.name')->label('Auteur'),
                    ]),
            ]);
    }

    private static function buildPreview(QrCode $record): string
    {
        $generator = app(QrCodeGenerator::class);
        $content = $generator->render($record);

        if (mb_strtolower($record->format ?? 'svg') === 'svg') {
            return $content;
        }

        $mime = $generator->mimeType($record);
        $base64 = base64_encode($content);

        return sprintf('<img src="data:%s;base64,%s" alt="QR Code" class="max-w-full h-auto" />', $mime, $base64);
    }
}
