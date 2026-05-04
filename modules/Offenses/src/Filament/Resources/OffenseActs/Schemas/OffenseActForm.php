<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\OffenseActs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class OffenseActForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
