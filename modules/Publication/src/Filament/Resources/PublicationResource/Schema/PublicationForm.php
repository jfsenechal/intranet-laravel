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
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('url')
                    ->label('URL')
                    ->helperText('Url du document sur le site deliberations.be')
                    ->required()
                    ->url()
                    ->columnSpanFull()
                    ->maxLength(255),
                Flex::make(
                    [
                        Select::make('category_id')
                            ->label('Categorie')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload(),
                        DateTimePicker::make('expire_date')
                            ->label('Date d\'expiration'),
                    ]
                ),
            ]);
    }
}
