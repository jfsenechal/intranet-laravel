<?php

namespace AcMarche\Mileage\Filament\Resources\Trips\Tables;

use AcMarche\Mileage\Filament\Resources\Trips\TripResource;
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
                Tables\Columns\IconColumn::make('declared')
                    ->label('Déclaré')
                    ->state(fn (Trip $record) => $record->isDeclared())
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('declared')
                    ->label('Déclaré')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('declaration_id'),
                        false: fn ($query) => $query->whereNull('declaration_id'),
                    )
                    ->default(false),
                Tables\Filters\SelectFilter::make('type_movement')
                    ->label('Type')
                    ->options([
                        'externe' => 'Externe',
                        'service' => 'Service',
                    ]),
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
