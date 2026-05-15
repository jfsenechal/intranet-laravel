<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Groupes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class GroupeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nom')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
