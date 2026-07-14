<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Diplomas\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

final class DiplomaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Diplôme')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('certificate_file')
                            ->label('Fichier attestation')
                            ->placeholder('—')
                            ->icon('heroicon-o-arrow-down-tray')
                            ->formatStateUsing(fn (?string $state): ?string => $state ? 'Télécharger' : null)
                            ->url(fn (?string $state): ?string => $state ? Storage::disk('local')->temporaryUrl($state, now()->addMinutes(5)) : null)
                            ->openUrlInNewTab(),
                    ]),
                Section::make('Métadonnées')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('user_add')
                            ->label('Par')
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('updated_by')
                            ->label('Par')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
