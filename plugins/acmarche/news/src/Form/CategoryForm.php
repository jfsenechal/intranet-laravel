<?php

namespace AcMarche\News\Form;

use Filament\Forms;
use Filament\Forms\Form;

class CategoryForm
{
    public static function createForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required()
            ]);
    }
}
