<?php

namespace AcMarche\Mileage\Filament\Resources\TripResource\Schema;

use AcMarche\Mileage\Models\Declaration;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TripForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations du déplacement')
                    ->schema([
                        Forms\Components\Select::make('declaration_id')
                            ->label('Déclaration')
                            ->relationship('declaration', 'last_name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('type_movement')
                            ->label('Type de déplacement')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('content')
                            ->label('Motif')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Trajet')
                    ->schema([
                        Forms\Components\TextInput::make('departure_location')
                            ->label('Lieu de départ')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('arrival_location')
                            ->label('Lieu d\'arrivée')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('distance')
                            ->label('Distance (km)')
                            ->required()
                            ->numeric()
                            ->suffix('km'),
                    ])
                    ->columns(3),

                Section::make('Dates et heures')
                    ->schema([
                        Forms\Components\DateTimePicker::make('departure_date')
                            ->label('Date/heure de départ')
                            ->required(),

                        Forms\Components\DateTimePicker::make('arrival_date')
                            ->label('Date/heure d\'arrivée'),

                        Forms\Components\TimePicker::make('start_time')
                            ->label('Heure de début'),

                        Forms\Components\TimePicker::make('end_time')
                            ->label('Heure de fin'),
                    ])
                    ->columns(2),

                Section::make('Frais')
                    ->schema([
                        Forms\Components\TextInput::make('rate')
                            ->label('Tarif kilométrique')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        Forms\Components\TextInput::make('omnium')
                            ->label('Omnium')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        Forms\Components\TextInput::make('meal_expense')
                            ->label('Frais de repas')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        Forms\Components\TextInput::make('train_expense')
                            ->label('Frais de train')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),
                    ])
                    ->columns(4),
            ]);
    }
}
