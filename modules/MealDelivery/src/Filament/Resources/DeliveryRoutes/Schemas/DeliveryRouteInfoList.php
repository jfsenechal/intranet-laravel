<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class DeliveryRouteInfoList
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            TextEntry::make('name')
                ->label('Nom')
                ->columnSpan(2),

            RepeatableEntry::make('activeClients')
                ->label(fn ($record) => 'Clients ('.$record->activeClients->count().')')
                ->schema([
                    TextEntry::make('last_name')
                        ->hiddenLabel(),

                    TextEntry::make('first_name')
                        ->hiddenLabel(),
                ])
                ->columns(2),
        ]);
    }
}
