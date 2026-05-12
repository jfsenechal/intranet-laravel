<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

final class ClientsRelationManager extends RelationManager
{
    protected static string $relationship = 'clients';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom'),
                Tables\Columns\TextColumn::make('Adresse'),
                Tables\Columns\TextColumn::make('route_position')
                    ->label('Position'),
            ])
            ->defaultPaginationPageOption(50)
            ->defaultSort('route_position')
            ->reorderable('route_position');
    }
}
