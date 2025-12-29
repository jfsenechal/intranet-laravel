<?php

namespace AcMarche\Hrm\Filament\Resources\Absences\Tables;

use AcMarche\Hrm\Filament\Resources\Absences\AbsenceResource;
use AcMarche\Hrm\Models\Absence;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class AbsenceTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('employee.last_name')
                    ->label('Agent')
                    ->formatStateUsing(fn (Absence $record) => $record->employee->last_name.' '.$record->employee->first_name)
                    ->searchable(['last_name', 'first_name'])
                    ->sortable()
                    ->url(fn (Absence $record) => AbsenceResource::getUrl('view', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Debut')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('reason')
                    ->label('Raison')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_closed')
                    ->label('Cloture')
                    ->boolean(),
                Tables\Columns\TextColumn::make('reminder_date')
                    ->label('Rappel')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_closed')
                    ->label('Cloture')
                    ->placeholder('Toutes')
                    ->trueLabel('Cloturees')
                    ->falseLabel('En cours'),
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
