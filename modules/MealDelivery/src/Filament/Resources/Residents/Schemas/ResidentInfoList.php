<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Residents\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ResidentInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('last_name')
                                    ->label('Nom'),

                                TextEntry::make('first_name')
                                    ->label('Prénom')
                                    ->placeholder('—'),

                                TextEntry::make('room')
                                    ->label('Chambre')
                                    ->placeholder('—'),

                                IconEntry::make('is_active')
                                    ->label('Actif')
                                    ->boolean(),
                            ]),

                        TextEntry::make('notes')
                            ->label('Remarques')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
