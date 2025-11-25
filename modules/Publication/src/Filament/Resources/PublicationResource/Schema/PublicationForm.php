<?php

namespace AcMarche\Publication\Filament\Resources\PublicationResource\Schema;

use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

class PublicationForm
{

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Flex::make(
                    [
                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('url')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('wpCategoryId')
                                    ->label('WP Category ID')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        Forms\Components\TextInput::make('url')
                            ->label('URL')
                            ->required()
                            ->url()
                            ->maxLength(255),

                        Forms\Components\DateTimePicker::make('expire_date')
                            ->label('Expire Date'),
                    ]
                ),
            ]);
    }
}
