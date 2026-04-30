<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ClaimRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('last_name')
                    ->label('Nom')
                    ->required(),
                TextInput::make('first_name')
                    ->label('Prénom')
                    ->required(),
                TextInput::make('street')
                    ->label('Rue')
                    ->required(),
                TextInput::make('postal_code')
                    ->label('Code postal')
                    ->required(),
                TextInput::make('city')
                    ->label('Localité')
                    ->required(),
                TextInput::make('iban')
                    ->label('Compte IBAN')
                    ->required()
                    ->rule('iban')
                    ->placeholder('BE00 0000 0000 0000')
                    ->helperText('Format: BE00 0000 0000 0000'),
                TextInput::make('amount')
                    ->label('Montant')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->prefix('€'),
                DatePicker::make('filing_date')
                    ->label('Date de la déclaration')
                    ->required(),
                Textarea::make('content')
                    ->label('Description')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
