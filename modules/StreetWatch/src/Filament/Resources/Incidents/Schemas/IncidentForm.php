<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\Incidents\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class IncidentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Identification')
                    ->columns(2)
                    ->schema([
                        TextInput::make('place')
                            ->label('Lieu')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('object')
                            ->label('Objet')
                            ->required()
                            ->maxLength(255),
                        Select::make('typeIncident_id')
                            ->label('Type')
                            ->relationship('typeIncident', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('requestBy_id')
                            ->label('Demandé par')
                            ->relationship('requestBy', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('occurred_date')
                            ->label("Date de l'incident")
                            ->seconds(false),
                    ]),

                Section::make('Contenu')
                    ->schema([
                        RichEditor::make('description')
                            ->label('Description')
                            ->required()
                            ->columnSpanFull(),
                        RichEditor::make('response')
                            ->label('Suite donnée')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
