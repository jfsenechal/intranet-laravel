<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Recipient\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class RecipientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('last_name')
                    ->label('Nom'),
                TextEntry::make('first_name')
                    ->label('Prénom'),
                TextEntry::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope'),
                IconEntry::make('ics')
                    ->label('ICS')
                    ->boolean(),
            ]);
    }
}
