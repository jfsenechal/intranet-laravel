<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Trips\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                            ->seconds(false)
                            // The hour and minute are only relevant for external movements,
                            // so display the time inputs when the three external fields are filled.
                            ->time(fn (Get $get): bool => filled($get('departure_location'))
                                && filled($get('arrival_location'))
                                && filled($get('arrival_date')))
                            // A trip date is a Belgian wall clock, not an instant: it is stored
                            // and read back verbatim, so the picker must not convert it.
                            ->timezone(config('app.timezone'))
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
                            ->maxLength(255)
                            ->minLength(2)
                            ->live(onBlur: true)
                            ->afterStateUpdated(self::defaultDepartureDateToArrivalDay(...))
                            ->requiredWith('arrival_location,arrival_date'),
                        TextInput::make('arrival_location')
                            ->label('Lieu d\'arrivée')
                            ->maxLength(255)
                            ->minLength(2)
                            ->live(onBlur: true)
                            ->afterStateUpdated(self::defaultDepartureDateToArrivalDay(...))
                            ->requiredWith('departure_location,arrival_date'),
                        DateTimePicker::make('arrival_date')
                            ->label('Date/heure de retour')
                            ->seconds(false)
                            ->live(onBlur: true)
                            // Stored verbatim like departure_date, see above.
                            ->timezone(config('app.timezone'))
                            ->afterStateUpdated(self::defaultDepartureDateToArrivalDay(...))
                            ->requiredWith('departure_location,arrival_location'),
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

    /**
     * An external movement is encoded from its arrival, so leaving the
     * departure empty only makes the beneficiary retype the same day: as soon
     * as the three external fields are filled, default it to the arrival day
     * at 08h00. A departure already encoded is never overwritten.
     */
    private static function defaultDepartureDateToArrivalDay(Get $get, Set $set): void
    {
        if (filled($get('departure_date'))) {
            return;
        }

        if (blank($get('departure_location')) || blank($get('arrival_location')) || blank($get('arrival_date'))) {
            return;
        }

        $set(
            'departure_date',
            Carbon::parse($get('arrival_date'))->setTime(8, 0)->format('Y-m-d H:i:s'),
        );
    }
}
