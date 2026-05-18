<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\TypesIncident\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class TypeIncidentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ]);
    }
}
