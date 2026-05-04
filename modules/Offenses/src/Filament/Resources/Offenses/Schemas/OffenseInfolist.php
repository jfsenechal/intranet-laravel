<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OffenseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Sanction')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('offenseAct.name')
                            ->label('Acte'),
                        TextEntry::make('decision_date')
                            ->label('Date de décision')
                            ->date('d/m/Y')
                            ->placeholder('—'),
                        TextEntry::make('fine_amount')
                            ->label('Amende')
                            ->money('EUR')
                            ->placeholder('—'),
                        IconEntry::make('mediation')
                            ->label('Médiation')
                            ->boolean(),
                        TextEntry::make('prosecutor_opinion')
                            ->label('Avis du procureur')
                            ->placeholder('—'),
                        TextEntry::make('file_name')
                            ->label('Fichier')
                            ->placeholder('—'),
                    ]),

                Section::make('Contrevenant')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('offender.last_name')
                            ->label('Nom'),
                        TextEntry::make('offender.first_name')
                            ->label('Prénom'),
                        TextEntry::make('offender.birth_date')
                            ->label('Date de naissance')
                            ->date('d/m/Y')
                            ->placeholder('—'),
                        TextEntry::make('offender.street')
                            ->label('Rue')
                            ->placeholder('—'),
                        TextEntry::make('offender.postal_code')
                            ->label('Code postal')
                            ->placeholder('—'),
                        TextEntry::make('offender.city')
                            ->label('Localité')
                            ->placeholder('—'),
                    ]),

                Section::make('Métadonnées')
                    ->columns(3)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('user_add')
                            ->label('Créé par')
                            ->placeholder('—'),
                        TextEntry::make('created_at')
                            ->label('Date de création')
                            ->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')
                            ->label('Dernière modification')
                            ->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
