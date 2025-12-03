<?php

namespace AcMarche\Document\Filament\Resources\DocumentResource\Schema;

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
                ImageEntry::make('file_path')
                    ->label('Fichier')
                    ->disk('public')
                    ->columnSpanFull()
                    ->visible(fn ($record): bool => $record->file_path && str_starts_with($record->file_mime ?? '', 'image/'))
                    ->afterContent(
                        Action::make('download')
                            ->label('Télécharger le fichier')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->url(fn ($record): string => Storage::disk('public')->url($record->file_path))
                            ->openUrlInNewTab()
                    ),
                TextEntry::make('file_name')
                    ->label('Fichier')
                    ->columnSpanFull()
                    ->icon('heroicon-o-document')
                    ->visible(fn ($record): bool => $record->file_path && ! str_starts_with($record->file_mime ?? '', 'image/'))
                    ->afterContent(
                        Action::make('download')
                            ->label('Télécharger le fichier')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->url(fn ($record): string => Storage::disk('public')->url($record->file_path))
                            ->openUrlInNewTab()
                    ),
            ]);
    }
}
