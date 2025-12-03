<?php

namespace AcMarche\Publication\Filament\Resources\PublicationResource\Schema;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

final class PublicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Flex::make(
                    [
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255),

                        Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('url')
                                    ->maxLength(255),
                                TextInput::make('wpCategoryId')
                                    ->label('WP Category ID')
                                    ->required()
                                    ->maxLength(255),
                            ]),

                        TextInput::make('url')
                            ->label('URL')
                            ->helperText('Url du document sur le site deliberations.be')
                            ->required()
                            ->url()
                            ->maxLength(255),

                        DateTimePicker::make('expire_date')
                            ->label('Expire Date'),
                    ]
                ),
            ]);
    }
}
