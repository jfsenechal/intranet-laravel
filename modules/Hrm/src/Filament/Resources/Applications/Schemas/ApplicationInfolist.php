<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Applications\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

final class ApplicationInfolist
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
                        Section::make('Candidature')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('received_at')
                                    ->label('Reçue le')
                                    ->date('d/m/Y'),
                                TextEntry::make('employer.name')
                                    ->label('Employeur')
                                    ->placeholder('—'),
                                TextEntry::make('jobFunction.name')
                                    ->label('Fonction')
                                    ->placeholder('—'),
                                TextEntry::make('mail_reference')
                                    ->label('Référence courrier')
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                        Fieldset::make("Type d'appel")
                            ->columns(3)
                            ->schema([
                                IconEntry::make('is_spontaneous')
                                    ->label('Spontanée')
                                    ->boolean(),
                                IconEntry::make('is_public_call')
                                    ->label('Appel public')
                                    ->boolean(),
                                IconEntry::make('is_priority')
                                    ->label('Prioritaire')
                                    ->boolean(),
                                TextEntry::make('public_call')
                                    ->label("Nom de l'appel public")
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                        Section::make('Fichier')
                            ->schema([
                                TextEntry::make('file')
                                    ->label('Fichier')
                                    ->placeholder('Aucun fichier')
                                    ->url(
                                        fn (?string $state): ?string => $state ? Storage::disk('public')->url($state) : null,
                                        shouldOpenInNewTab: true,
                                    ),
                            ]),
                        Section::make('Notes')
                            ->schema([
                                TextEntry::make('notes')
                                    ->label('Notes')
                                    ->hiddenLabel()
                                    ->html()
                                    ->prose()
                                    ->placeholder('—')
                                    ->columnSpanFull(),
                            ]),
                    ]),
                Section::make('Suivi')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('updated_by')
                            ->label('Modifié par')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
