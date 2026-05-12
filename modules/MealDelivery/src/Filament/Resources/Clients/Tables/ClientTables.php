<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Clients\Tables;

use AcMarche\MealDelivery\Models\Client;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ClientTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('last_name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('address')
                    ->label('Adresse')
                    ->state(fn (Client $record): string => mb_trim($record->street.' '.$record->number.', '.$record->postal_code.' '.$record->city))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->where('street', 'like', "%{$search}%")
                        ->orWhere('number', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%"))
                    ->toggleable(),

                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->toggleable(),

                TextColumn::make('deliveryRoute.name')
                    ->label('Route')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('use_cafeteria')
                    ->label('Cafeteria')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Active clients')
                    ->query(fn (Builder $query) => $query->where('is_active', true))
                    ->default(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
