<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Trips\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class TripForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informations du déplacement')
                    ->schema([
                        TextInput::make('distance')
                            ->label('Distance (km)(Aller,Retour)')
                            ->required()
                            ->numeric()
                            ->suffix('km'),
                        DateTimePicker::make('departure_date')
                            ->label('Date du déplacement')
                            ->date()
                            ->required(),
                        Textarea::make('content')
                            ->label('Détail des courses')
                            ->helperText('Maximum 80 caractères')
                            ->maxLength(80)
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Si déplacement externe')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('warning')
                    ->description('Si vous avez quitté la zone 6900')
                    ->schema([
                        TextInput::make('departure_location')
                            ->label('Lieu de départ')
                            ->maxLength(255),
                        TextInput::make('arrival_location')
                            ->label('Lieu d\'arrivée')
                            ->maxLength(255),
                        DateTimePicker::make('arrival_date')
                            ->label('Date/heure d\'arrivée'),
                        TextInput::make('meal_expense')
                            ->label('Frais de repas')
                            ->helperText('Max 12,30 euros')
                            ->numeric()
                            ->maxValue(12.3)
                            ->step(0.01)
                            ->prefix('€'),
                        TextInput::make('train_expense')
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
