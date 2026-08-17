<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contracts\Tables;

use AcMarche\Hrm\Enums\ContractStatusEnum;
use AcMarche\Hrm\Filament\Actions\ReplicateContractAction;
use AcMarche\Hrm\Filament\Filters\ContractNatureFilter;
use AcMarche\Hrm\Filament\Filters\ContractTypeFilter;
use AcMarche\Hrm\Filament\Filters\DirectionFilter;
use AcMarche\Hrm\Filament\Filters\EmployerFilter;
use AcMarche\Hrm\Filament\Filters\PayScaleFilter;
use AcMarche\Hrm\Filament\Filters\ServiceFilter;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ContractTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('employee.last_name')
                    ->label('Agent')
                    ->formatStateUsing(
                        fn (Contract $record): string => $record->employee->last_name.' '.$record->employee->first_name
                    )
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),
                TextColumn::make('employer.name')
                    ->label('Employeur')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('contractNature.name')
                    ->label('Nature')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('contractType.name')
                    ->label('Type')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('payScale.name')
                    ->label('Echelle')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('job_title')
                    ->label('Fonction')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('start_date')
                    ->label('Débute le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('end_date')
                    ->label('Prend fin le')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('replaces.last_name')
                    ->label('Remplace')
                    ->state(fn (Contract $record): ?string => $record->replaces?->full_name)
                    ->searchable(['last_name', 'first_name'])
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('work_regime')
                    ->label('Regime')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_closed')
                    ->label('Clôturé')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filtersFormColumns(2)
            ->persistFiltersInSession()
            ->filters([
                EmployerFilter::make(),
                DirectionFilter::make(),
                ServiceFilter::make(),
                ContractNatureFilter::make(),
                ContractTypeFilter::make(),
                PayScaleFilter::make(),
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(ContractStatusEnum::class),
                SelectFilter::make('replaces')
                    ->label('Remplace')
                    // Only the agents actually replaced by a contract are worth listing.
                    ->relationship(
                        'replaces',
                        'last_name',
                        fn (Builder $query): Builder => $query->whereIn(
                            'id',
                            Contract::query()->whereNotNull('replaces_id')->select('replaces_id'),
                        ),
                    )
                    ->getOptionLabelFromRecordUsing(fn (Employee $record): string => $record->full_name)
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_closed')
                    ->label('Clôturé')
                    ->placeholder('Tous')
                    ->trueLabel('Clôturés')
                    ->falseLabel('En cours')
                    ->default(false),
                TernaryFilter::make('is_amendment')
                    ->label('Avenant')
                    ->placeholder('Tous')
                    ->trueLabel('Avenants')
                    ->falseLabel('Non avenants'),
                TernaryFilter::make('is_suspended')
                    ->label('Suspendu')
                    ->placeholder('Tous')
                    ->trueLabel('Suspendus')
                    ->falseLabel('Non suspendus'),
                TernaryFilter::make('is_replacement')
                    ->label('Remplacement')
                    ->placeholder('Tous')
                    ->trueLabel('Remplacements')
                    ->falseLabel('Non remplacements'),
                TernaryFilter::make('end_date_expired')
                    ->label('Échéance dépassée')
                    ->placeholder('Tous')
                    ->trueLabel('Dépassée')
                    ->falseLabel('À venir ou sans fin')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereDate('end_date', '<', today()),
                        false: fn (Builder $query): Builder => $query->where(
                            fn (Builder $query) => $query
                                ->whereDate('end_date', '>=', today())
                                ->orWhereNull('end_date'),
                        ),
                    ),
            ], layout: FiltersLayout::Modal)
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

    public static function relation(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('employer.name')
                    ->label('Employeur')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('contractNature.name')
                    ->label('Nature')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('contractType.name')
                    ->label('Type')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('payScale.name')
                    ->label('Echelle')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('job_title')
                    ->label('Fonction')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('start_date')
                    ->label('Début')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('replaces.last_name')
                    ->label('Remplace')
                    ->state(fn (Contract $record): ?string => $record->replaces?->full_name)
                    ->searchable(['last_name', 'first_name'])
                    ->sortable()
                    ->toggleable()
                    ->placeholder('—'),
                TextColumn::make('work_regime')
                    ->label('Régime')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_closed')
                    ->label('Clôturé')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_closed')
                    ->label('Clôturé')
                    ->placeholder('Tous')
                    ->trueLabel('Clôturés')
                    ->falseLabel('En cours')
                    ->default(false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                ReplicateContractAction::make(),
            ])
            ->recordAction(ViewAction::class);
    }
}
