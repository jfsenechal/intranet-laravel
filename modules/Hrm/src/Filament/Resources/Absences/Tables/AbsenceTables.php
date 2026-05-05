<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Absences\Tables;

use AcMarche\Hrm\Enums\ReasonsEnum;
use AcMarche\Hrm\Filament\Filters\ContractActiveFilter;
use AcMarche\Hrm\Models\Absence;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Components\Flex;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class AbsenceTables
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
                        fn(Absence $record): string => $record->employee->last_name.' '.$record->employee->first_name
                    )
                    ->searchable(['last_name', 'first_name'])
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('start_date')
                    ->label('Debut')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('reason')
                    ->label('Raison')
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('is_closed')
                    ->label('Clôturée')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reminder_date')
                    ->label('Rappel')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersFormColumns(2)
            ->persistFiltersInSession()
            ->filters([
                SelectFilter::make('reason')
                    ->label('Raison')
                    ->options(ReasonsEnum::class)
                    ->columnSpanFull(),
                Filter::make('period')
                    ->label('Période')
                    ->schema([
                        Flex::make([
                            DatePicker::make('from')
                                ->label('Du'),
                            DatePicker::make('until')
                                ->label('Au'),
                        ]),
                    ])
                    ->columnSpanFull()
                    ->query(fn(Builder $query, array $data): Builder => $query
                        ->when(
                            $data['from'] ?? null,
                            fn(Builder $query, $date): Builder => $query->whereDate('end_date', '>=', $date),
                        )
                        ->when(
                            $data['until'] ?? null,
                            fn(Builder $query, $date): Builder => $query->whereDate('start_date', '<=', $date),
                        )),
                TernaryFilter::make('is_closed')
                    ->label('Clôturée')
                    ->placeholder('Toutes')
                    ->trueLabel('Clôturées')
                    ->falseLabel('En cours')
                    ->default(false),
                ContractActiveFilter::makeWithContracts(),
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
                TextColumn::make('start_date')
                    ->label('Debut')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Raison')
                    ->searchable(),
                TextColumn::make('reminder_date')
                    ->label('Rappel')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('is_closed')
                    ->label('Clôturée')
                    ->boolean(),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->recordAction(ViewAction::class);
    }
}
