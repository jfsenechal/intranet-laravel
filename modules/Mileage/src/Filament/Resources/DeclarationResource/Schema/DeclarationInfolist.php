<?php

namespace AcMarche\Mileage\Filament\Resources\DeclarationResource\Schema;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class DeclarationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('category.name')
                    ->label('Catégorie')
                    ->badge()
                    ->columnSpanFull(),
            ]);
    }
}
