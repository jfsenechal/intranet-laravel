<?php

namespace AcMarche\Document\Filament\Resources\DocumentResource\Schema;

use AcMarche\App\Filament\Schema\Infolists\PdfViewerEntry;
use AcMarche\Document\Models\Document;
use Filament\Actions\Action;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

final class DocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('category.name')
                    ->label('Catégorie')
                    ->columnSpanFull(),
                TextEntry::make('content')
                    ->label('Description')
                    ->html()
                    ->columnSpanFull()
                    ->prose()
                    ->hidden(fn ($state): bool => blank($state)),
                TextEntry::make('download_link')
                    ->label('Fichier')
                    ->columnSpanFull()
                    ->afterContent(
                        Action::make('download')
                            ->label('Télécharger le fichier')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->url(fn (Document $record): string => Storage::disk('public')->url($record->file_path))
                            ->openUrlInNewTab()
                    ),
                PdfViewerEntry::make('file_path')
                    ->label('Aperçu')
                    ->minHeight('40svh')
                    ->columnSpanFull()
                    ->disk('public'),
            ]);
    }
}
