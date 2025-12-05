<?php

namespace AcMarche\Mileage\Filament\Resources\PersonalInformation\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;

final class PersonalInformationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                DatePicker::make('college_trip_date')
                    ->label('Date de la décision du Collège')
                    ->required(),
                Checkbox::make('omnium')
                    ->label('Retenue omnium')
                    ->helperText('Cochez pour oui'),
            ]);
    }
}
