<?php

namespace AcMarche\Security\Form;

use Filament\Forms;
use Filament\Forms\Form;

class RoleForm
{
    public static function createForm(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->default('ROLE_NOM_MODULE_NOM_ROLE')
                    ->required()
                    ->maxLength(100)
                    ->helperText('Le nom doit avoir le format: ROLE_NOM_MODULE_NOM_ROLE')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255)
                    ->required(),
            ]);
    }
}
