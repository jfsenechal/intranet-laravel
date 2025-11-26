<?php

namespace AcMarche\Mileage\Filament\Resources\TripResource\Tables;

use AcMarche\Mileage\Filament\Resources\TripResource;
use AcMarche\Mileage\Models\Trip;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

class TripTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('date_depart', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('date_depart')
                    ->label('Date')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->url(fn (Trip $record) => TripResource::getUrl('view', ['record' => $record->id])),

                Tables\Columns\TextColumn::make('lieu_depart')
                    ->label('Départ')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('lieu_arrive')
                    ->label('Arrivée')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('distance')
                    ->label('Distance')
                    ->sortable()
                    ->suffix(' km'),

                Tables\Columns\TextColumn::make('type_deplacement')
                    ->label('Type')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('declaration.nom')
                    ->label('Déclarant')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('tarif')
                    ->label('Tarif')
                    ->money('EUR')
                    ->toggleable(),

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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
