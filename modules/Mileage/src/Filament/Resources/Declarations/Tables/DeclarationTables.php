<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Declarations\Tables;

use AcMarche\Mileage\Calculator\DeclarationCalculator;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Repository\DeclarationRepository;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class DeclarationTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(fn(Builder $query) => DeclarationRepository::getByUser($query)->with('trips'))
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('car_license_plate1')
                    ->label('Plaque')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type_movement')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date()
                    ->sortable(),
                TextColumn::make('trips_count')
                    ->label('Déplacements')
                    ->counts('trips')
                    ->sortable(),
                TextColumn::make('totalKilometers')
                    ->label('Nombre de km')
                    ->state(function (Declaration $record): float {
                        $record->loadMissing('trips');
                        $calculator = new DeclarationCalculator($record);

                        return $calculator->calculate()->totalKilometers;
                    })
                    ->suffix('km'),
                TextColumn::make('totalRefund')
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
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
