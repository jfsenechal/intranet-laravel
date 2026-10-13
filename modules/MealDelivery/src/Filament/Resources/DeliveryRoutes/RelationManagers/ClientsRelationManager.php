<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\DeliveryRoutes\RelationManagers;

use AcMarche\MealDelivery\Models\Client;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

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
            ->modifyQueryUsing(fn (Builder $query): Builder => $query
                ->where('is_active', true)
                ->where('use_cafeteria', false))
            ->columns([
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom'),
                Tables\Columns\TextColumn::make('address')
                    ->label('Adresse')
                    ->state(fn (Client $record): string => mb_trim("{$record->street} {$record->number}, {$record->city}")),
                Tables\Columns\TextColumn::make('route_position')
                    ->label('Position'),
            ])
            ->defaultPaginationPageOption(50)
            ->defaultSort('route_position')
            ->reorderable('route_position');
    }
}
