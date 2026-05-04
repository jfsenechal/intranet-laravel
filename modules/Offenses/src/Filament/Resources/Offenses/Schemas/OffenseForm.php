<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses\Schemas;

use AcMarche\Offenses\Models\Offender;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OffenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Sanction')
                    ->schema([
                        Select::make('offender_id')
                            ->label('Contrevenant')
                            ->relationship('offender', 'last_name')
                            ->getOptionLabelFromRecordUsing(
                                fn (Offender $record): string => mb_trim($record->last_name.' '.$record->first_name)
                            )
                            ->searchable(['last_name', 'first_name'])
                            ->preload()
                            ->required(),

                        Select::make('offense_act_id')
                            ->label('Acte')
                            ->relationship('offenseAct', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        DatePicker::make('decision_date')
                            ->label('Date de décision')
                            ->nullable(),

                        TextInput::make('fine_amount')
                            ->label('Amende (€)')
                            ->numeric()
                            ->step(0.01)
                            ->nullable(),

                        Toggle::make('mediation')
                            ->label('Médiation')
                            ->default(false),

                        TextInput::make('prosecutor_opinion')
                            ->label('Avis du procureur')
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('file_name')
                            ->label('Fichier')
                            ->maxLength(255)
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
