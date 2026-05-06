<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders\Schemas;

use AcMarche\Offenses\Filament\Resources\Offenses\OffenseResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

final class OffenderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Coordonnées')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('offender.birth_date')
                            ->label('Date de naissance')
                            ->date('d/m/Y')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('offender.street')
                            ->label('Rue')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('offender.postal_code')
                            ->label('Code postal')
                            ->placeholder('—'),
                        TextEntry::make('offender.city')
                            ->label('Localité')
                            ->placeholder('—'),
                    ]),
                Section::make('Incivilités')
                    ->schema([
                        RepeatableEntry::make('offenses')
                            ->schema([
                                TextEntry::make('offenseAct.name')
                                    ->label('Acte')
                                    ->url(
                                        fn (Model $record): string => OffenseResource::getUrl(
                                            'view',
                                            ['record' => $record->id]
                                        )
                                    ),
                                TextEntry::make('fine_amount')
                                    ->label('Amende')
                                    ->money('EUR')
                                    ->placeholder('—'),
                                IconEntry::make('mediation'),
                                TextEntry::make('decision_date')
                                    ->label('Date de décision')
                                    ->date('d/m/Y'),
                            ])
                            ->columns(2)
                            ->grid(2),
                    ]),
            ]);
    }
}
