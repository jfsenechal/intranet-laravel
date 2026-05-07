<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\HrDocuments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

final class HrDocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Document')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Intitulé'),
                        TextEntry::make('file_name')
                            ->label('Fichier')
                            ->placeholder('—')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->formatStateUsing(fn (?string $state): ?string => $state ? 'Télécharger' : null)
                            ->url(fn (?string $state): ?string => $state ? Storage::disk('public')->url($state) : null)
                            ->openUrlInNewTab(),
                        TextEntry::make('notes')
                            ->label('Remarques')
                            ->html()
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->date('d/m/Y'),
                        TextEntry::make('updated_by')
                            ->label('Modifié par')
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->date('d/m/Y'),
                    ]),
            ]);
    }
}
