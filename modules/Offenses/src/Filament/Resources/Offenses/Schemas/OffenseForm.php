<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class OffenseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Flex::make([
                    Section::make([
                        Hidden::make('offender_id'),
                        Select::make('offense_act_id')
                            ->label('Acte')
                            ->relationship('offenseAct', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('prosecutor_opinion')
                            ->label('Avis du procureur')
                            ->maxLength(255)
                            ->nullable(),
                        FileUpload::make('file_name')
                            ->label('Fichier')
                            ->disk('public')
                            ->directory(config('offenses.uploads.offenses'))
                            ->previewable(false)
                            ->downloadable()
                            ->maxSize(10240)
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                        ->columns(2),
                    Section::make([
                        DatePicker::make('decision_date')
                            ->label('Date de décision')
                            ->nullable(),
                        Toggle::make('mediation')
                            ->label('Médiation')
                            ->default(false),
                        TextInput::make('fine_amount')
                            ->label('Amende (€)')
                            ->numeric()
                            ->step(0.01)
                            ->nullable(),

                    ])->grow(false),
                ])
                    ->columnSpanFull()
                    ->from('md'),
            ]);
    }
}
