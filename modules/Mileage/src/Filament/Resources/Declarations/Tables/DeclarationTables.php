<?php

namespace AcMarche\Mileage\Filament\Resources\Declarations\Tables;

use AcMarche\Mileage\Calculator\DeclarationCalculator;
use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Repository\DeclarationRepository;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class DeclarationTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(fn (Builder $query) => DeclarationRepository::getByUser($query)->with('trips'))
            ->columns([
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Declaration $record) => DeclarationResource::getUrl('view', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('car_license_plate1')
                    ->label('Plaque')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('type_movement')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('trips_count')
                    ->label('Déplacements')
                    ->counts('trips')
                    ->sortable(),
                Tables\Columns\TextColumn::make('totalKilometers')
                    ->label('Nombre de km')
                    ->state(function (Declaration $record): float {
                        $record->loadMissing('trips');
                        $calculator = new DeclarationCalculator($record);

                        return $calculator->calculate()->totalKilometers;
                    })
                    ->suffix('km'),
                Tables\Columns\TextColumn::make('totalRefund')
                    ->label('Total à rembourser')
                    ->state(function (Declaration $record): float {
                        $record->loadMissing('trips');
                        $calculator = new DeclarationCalculator($record);

                        return $calculator->calculate()->totalRefund;
                    })
                    ->money('EUR'),
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
