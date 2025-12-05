<?php

namespace AcMarche\Mileage\Filament\Resources\PersonalInformation\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class PersonalInformationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('car_license_plate1')
                    ->label('Numéro de plaque 1')
                    ->required(),
                TextInput::make('car_license_plate2')
                    ->label('Numéro de plaque 2'),
                TextInput::make('street')
                    ->label('Rue')
                    ->required(),
                TextInput::make('postal_code')
                    ->label('Code postal')
                    ->required(),
                TextInput::make('city')
                    ->label('Localité')
                    ->required(),
                TextInput::make('college_trip_date')
                    ->label('Date de collège')
                    ->required(),
            ]);
    }
}
