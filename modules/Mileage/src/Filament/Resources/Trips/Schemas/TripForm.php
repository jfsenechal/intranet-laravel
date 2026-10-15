<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Trips\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Carbon;

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
                            ->helperText('')
                            ->date()
                            ->time(false)
                            ->seconds(false)
                            // Only the day is encoded: store it at 08h00 rather than midnight.
                            ->dehydrateStateUsing(fn (?string $state): ?string => filled($state)
                                ? Carbon::parse($state)->setTime(8, 0)->format('Y-m-d H:i:s')
                                : null)
                            // A trip date is a Belgian wall clock, not an instant: it is stored
                            // and read back verbatim, so the picker must not convert it.
                            ->timezone(config('app.timezone'))
                            ->required(),
                        TextInput::make('departure_location')
                            ->label('Lieu de départ')
                            ->maxLength(255)
                            ->minLength(2)
                            ->live(onBlur: true)
                            ->requiredWith('arrival_location'),
                        TextInput::make('arrival_location')
                            ->label('Lieu d\'arrivée')
                            ->maxLength(255)
                            ->minLength(2)
                            ->live(onBlur: true)
                            ->requiredWith('departure_location'),
                        Textarea::make('content')
                            ->label('Objet.')
                            ->helperText(' (Formation, suivi de chantier, autre lieu de prestation, ...),Maximum 80 caractères')
                            ->maxLength(80)
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Si vous avez quitté la zone 6900')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('danger')
                    ->schema([

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
