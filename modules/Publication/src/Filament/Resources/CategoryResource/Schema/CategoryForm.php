<?php

namespace AcMarche\Publication\Filament\Resources\CategoryResource\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Flex::make([
                    TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('url')
                        ->label('URL')
                        ->maxLength(255),

                    TextInput::make('wpCategoryId')
                        ->label('WP Category ID')
                        ->required()
                        ->maxLength(255),
                ]),
            ]);
    }
}
