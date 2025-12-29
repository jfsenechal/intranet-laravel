<?php

namespace AcMarche\Publication\Filament\Resources\Categories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('url')
                    ->label('Url marche.be')
                    ->required()
                    ->url()
                    ->maxLength(255),
                TextInput::make('wpCategoryId')
                    ->label('Numéro de la catégorie wordpress')
                    ->required()
                    ->integer()
                    ->maxLength(10),
            ]);
    }
}
