<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Groups\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class GroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
            ]);
    }
}
