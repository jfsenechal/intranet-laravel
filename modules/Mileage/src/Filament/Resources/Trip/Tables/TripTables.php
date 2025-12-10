<?php

namespace AcMarche\Mileage\Filament\Resources\Trip\Tables;

use AcMarche\Mileage\Filament\Resources\Trip\TripResource;
use AcMarche\Mileage\Models\Trip;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class TripTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('departure_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->url(fn (Trip $record) => TripResource::getUrl('view', ['record' => $record->id])),

                Tables\Columns\TextColumn::make('departure_location')
                    ->label('Départ')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\TextColumn::make('arrival_location')
                    ->label('Arrivée')
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('distance')
                    ->label('Distance')
                    ->sortable()
                    ->suffix(' km'),
                Tables\Columns\TextColumn::make('type_movement')
                    ->label('Type')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('declaration.last_name')
                    ->label('Déclaré ?'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
