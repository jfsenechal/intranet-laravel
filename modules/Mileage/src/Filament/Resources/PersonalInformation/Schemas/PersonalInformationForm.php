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
                    ->label('Plaque d\'immatriculation 1')
                    ->required(),
                TextInput::make('car_license_plate2')
                    ->label('Plaque d\'immatriculation 2'),
                TextInput::make('street')
                    ->label('Rue')
                    ->required(),
                TextInput::make('postal_code')
                    ->label('Code postal')
                    ->required(),
                TextInput::make('city')
                    ->label('Localité')
                    ->required(),
                TextInput::make('iban')
                    ->label('Compte IBAN')
                    ->required()
                    ->rule('iban')
                    ->placeholder('BE00 0000 0000 0000')
                    ->helperText('Format: BE00 0000 0000 0000'),
            ]);
    }
}
