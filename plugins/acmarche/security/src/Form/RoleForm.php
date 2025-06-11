<?php

namespace AcMarche\Security\Form;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RoleForm
{
    public static function createForm(Schema $form): Schema
    {
        return $form
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
