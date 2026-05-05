<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Trainings\Tables;

use AcMarche\Hrm\Enums\TrainingTypeEnum;
use AcMarche\Hrm\Filament\Filters\ContractActiveFilter;
use AcMarche\Hrm\Filament\Filters\EmployerFilter;
use AcMarche\Hrm\Models\Training;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Query\Builder;

final class TrainingTables
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
                        fn (Training $record): string => $record->employee->last_name.' '.$record->employee->first_name
                    )
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Intitule')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('training_type')
                    ->label('Type')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('start_date')
                    ->label('Debut')
                    ->date('d/m/Y')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('duration_minutes')
                    ->label('Durée')
                    ->formatStateUsing(fn (?int $state): string => Training::formatDuration($state))
                    ->summarize(
                        Summarizer::make()
                            ->label('Total')
                            ->using(
                                fn (Builder $query): string => Training::formatDuration(
                                    (int) $query->sum('duration_minutes')
                                )
                            )
                    )
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('certificate_received')
                    ->label('Attestation')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('is_closed')
                    ->label('Cloture')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersFormColumns(2)
            ->persistFiltersInSession()
            ->filters([
                SelectFilter::make('training_type')
                    ->label('Type')
                    ->options(TrainingTypeEnum::class),
                EmployerFilter::makeThrough('employee.contracts'),
                ContractActiveFilter::makeWithContracts(),
                TernaryFilter::make('certificate_received')
                    ->label('Attestation recue')
                    ->placeholder('Toutes')
                    ->trueLabel('Recues')
                    ->falseLabel('Non recues'),
                TernaryFilter::make('is_closed')
                    ->label('Clôturée')
                    ->placeholder('Toutes')
                    ->trueLabel('Clôturées')
                    ->falseLabel('En cours'),
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
                TextColumn::make('name')
                    ->label('Intitule')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                TextColumn::make('training_type')
                    ->label('Type')
                    ->badge()
                    ->toggleable(),
                TextColumn::make('start_date')
                    ->label('Debut')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('duration_minutes')
                    ->label('Durée')
                    ->formatStateUsing(fn (?int $state): string => Training::formatDuration($state))
                    ->summarize(
                        Summarizer::make()
                            ->label('Total')
                            ->using(
                                fn (Builder $query): string => Training::formatDuration(
                                    (int) $query->sum('duration_minutes')
                                )
                            )
                    )
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('certificate_received')
                    ->label('Attestation')
                    ->boolean(),
                IconColumn::make('is_closed')
                    ->label('Cloture')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('training_type')
                    ->label('Type')
                    ->options(TrainingTypeEnum::class),
                TernaryFilter::make('certificate_received')
                    ->label('Attestation recue')
                    ->placeholder('Toutes')
                    ->trueLabel('Recues')
                    ->falseLabel('Non recues'),
                TernaryFilter::make('is_closed')
                    ->label('Cloture')
                    ->placeholder('Toutes')
                    ->trueLabel('Cloturees')
                    ->falseLabel('En cours'),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->recordAction(ViewAction::class);
    }
}
