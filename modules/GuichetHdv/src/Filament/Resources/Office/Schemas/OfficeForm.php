<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Office\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class OfficeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                TextInput::make('service')
                    ->label('Service')
                    ->maxLength(255),
            ]);
    }
}
