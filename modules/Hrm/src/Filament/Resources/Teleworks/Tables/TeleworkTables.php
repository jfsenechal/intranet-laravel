<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Teleworks\Tables;

use AcMarche\Hrm\Enums\DayTypeEnum;
use AcMarche\Hrm\Enums\LocationTypeEnum;
use AcMarche\Hrm\Models\Telework;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class TeleworkTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('employee:id,username,last_name,first_name'))
            ->columns([
                TextColumn::make('user_add')
                    ->label('Agent')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('employee_full_name')
                    ->label('Nom complet')
                    ->state(fn (Telework $record): string => $record->employee
                        ? mb_trim($record->employee->last_name.' '.$record->employee->first_name)
                        : '')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas(
                        'employee',
                        fn (Builder $query) => $query
                            ->where('last_name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%"),
                    ))
                    ->sortable(),
                TextColumn::make('location_type')
                    ->label('Lieu')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('day_type')
                    ->label('Type de jour')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('fixed_day')
                    ->label('Jour fixe')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('manager_validated')
                    ->label('Validé par la direction')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('manager_validated_at')
                    ->label('Validé le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('hr_validator_name')
                    ->label('Validation Grh par')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersFormColumns(2)
            ->persistFiltersInSession()
            ->filters([
                TernaryFilter::make('manager_validated')
                    ->label('Validation direction')
                    ->placeholder('Toutes')
                    ->trueLabel('Validées')
                    ->falseLabel('En attente'),
                SelectFilter::make('location_type')
                    ->label('Lieu')
                    ->options(LocationTypeEnum::class),
                SelectFilter::make('day_type')
                    ->label('Type de jour')
                    ->options(DayTypeEnum::class),
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
