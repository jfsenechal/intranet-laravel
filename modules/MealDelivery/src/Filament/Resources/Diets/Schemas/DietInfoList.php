<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Diets\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class DietInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nom'),
                    ]),

                Section::make('Clients')
                    ->schema([
                        RepeatableEntry::make('clients')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('Nom'),
                                TableColumn::make('Prénom'),
                                TableColumn::make('Localité'),
                                TableColumn::make('Téléphone'),
                            ])
                            ->schema([
                                TextEntry::make('last_name'),
                                TextEntry::make('first_name'),
                                TextEntry::make('city'),
                                TextEntry::make('phone')
                                    ->placeholder('—'),
                            ]),
                    ]),
            ]);
    }
}
