<?php

namespace AcMarche\Security\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->label('Nom')
                    ->default('ROLE_NOM_MODULE_NOM_ROLE')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Le nom doit avoir le format: ROLE_NOM_MODULE_NOM_ROLE')
                    ->columnSpanFull(),
                TextInput::make('description')
                    ->maxLength(255)
                    ->required(),
            ]);
    }
}
