<?php

namespace AcMarche\Document\Form;

use Filament\Forms;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function createForm(Schema $form): Schema
    {
        return $form
            ->components([
                Forms\Components\TextInput::make('name')
                    ->label('Nom')
                    ->required(),
            ]);
    }
}
