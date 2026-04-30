<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Valorizations\Schemas;

use AcMarche\Hrm\Models\Valorization;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

final class ValorizationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Grid::make()
                    ->columnSpan(2)
                    ->columns(1)
                    ->schema([
                        Section::make('Valorisation')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('employer_name')
                                    ->label('Employeur'),
                                TextEntry::make('duration')
                                    ->label('Durée'),
                                TextEntry::make('regime')
                                    ->label('Régime')
                                    ->placeholder('—'),
                            ]),
                        Section::make('Contenu')
                            ->schema([
                                TextEntry::make('content')
                                    ->label('Contenu')
                                    ->hiddenLabel()
                                    ->html()
                                    ->prose()
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Attestation')
                            ->schema([
                                TextEntry::make('file_name')
                                    ->label('Fichier attestation')
                                    ->placeholder('—')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->formatStateUsing(fn (?string $state): ?string => $state ? 'Télécharger' : null)
                                    ->url(fn (?string $state): ?string => $state ? Storage::disk('public')->url($state) : null)
                                    ->openUrlInNewTab(),
                            ]),
                    ]),
                Section::make('Métadonnées')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('employee.last_name')
                            ->label('Agent')
                            ->formatStateUsing(
                                fn ($state, Valorization $record): string => $record->employee?->last_name.' '.$record->employee?->first_name
                            ),
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
