<?php

namespace AcMarche\Mileage\Filament\Resources\Trip\Schema;

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
                        Forms\Components\TextInput::make('distance')
                            ->label('Distance (km)(Aller,Retour)')
                            ->required()
                            ->numeric()
                            ->suffix('km'),
                        Forms\Components\DateTimePicker::make('departure_date')
                            ->label('Date du déplacement')
                            ->date()
                            ->required(),
                        Forms\Components\Textarea::make('content')
                            ->label('Détail des courses')
                            ->helperText('Maximum 80 caractères')
                            ->maxLength(80)
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Si déplacement externe')
                    ->description('Si vous avez quitté la zone 6900')
                    ->schema([
                        Forms\Components\TextInput::make('departure_location')
                            ->label('Lieu de départ')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('arrival_location')
                            ->label('Lieu d\'arrivée')
                            ->maxLength(255),
                        Forms\Components\DateTimePicker::make('arrival_date')
                            ->label('Date/heure d\'arrivée'),
                        Forms\Components\TextInput::make('meal_expense')
                            ->label('Frais de repas')
                            ->helperText('Max 12,30 euros')
                            ->numeric()
                            ->maxValue(12.3)
                            ->step(0.01)
                            ->prefix('€'),
                        Forms\Components\TextInput::make('train_expense')
                            ->label('Frais de train ou de parking')
                            ->helperText('<!> Souche')
                            ->numeric()
                            ->columnSpan(2)
                            ->step(0.01)
                            ->prefix('€'),
                    ])
                    ->columns(3),
            ]);
    }
}
