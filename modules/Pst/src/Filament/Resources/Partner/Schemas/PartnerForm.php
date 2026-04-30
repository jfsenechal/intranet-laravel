<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Partner\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class PartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            //  ->fill($record->attributesToArray())
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('initials')
                    ->default(null)
                    ->maxLength(30),
                TextInput::make('phone')
                    ->label('Téléphone')
                    ->tel()
                    ->maxLength(255)
                    ->default(null),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255)
                    ->default(null),
                Textarea::make('description')
                    ->columnSpanFull(),
            ]);
    }
}
