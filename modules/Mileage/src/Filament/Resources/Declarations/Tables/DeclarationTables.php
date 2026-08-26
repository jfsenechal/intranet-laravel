<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Declarations\Tables;

use AcMarche\Mileage\Calculator\DeclarationCalculator;
use AcMarche\Mileage\Enums\TypeMovementEnum;
use AcMarche\Mileage\Models\Declaration;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class DeclarationTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(fn (Builder $query) => $query->with('trips'))
            ->columns([
                TextColumn::make('id')
                    ->label('Id')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('type_movement')
                    ->label('Type de déplacement')
                    ->options(TypeMovementEnum::class),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Créé depuis'),
                        DatePicker::make('created_until')
                            ->label('Créé jusqu\'à'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when(
                            $data['created_from'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                        )
                        ->when(
                            $data['created_until'] ?? null,
                            fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                        )),
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
