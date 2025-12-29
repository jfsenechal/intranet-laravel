<?php

namespace AcMarche\Hrm\Filament\Resources\Trainings\Tables;

use AcMarche\Hrm\Filament\Resources\Trainings\TrainingResource;
use AcMarche\Hrm\Models\Training;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class TrainingTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('employee.last_name')
                    ->label('Agent')
                    ->formatStateUsing(fn (Training $record) => $record->employee->last_name.' '.$record->employee->first_name)
                    ->searchable(['last_name', 'first_name'])
                    ->sortable()
                    ->url(fn (Training $record) => TrainingResource::getUrl('view', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('title')
                    ->label('Intitule')
                    ->searchable()
                    ->sortable()
                    ->limit(40),
                Tables\Columns\TextColumn::make('training_type')
                    ->label('Type')
                    ->badge()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Debut')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('duration_hours')
                    ->label('Duree')
                    ->suffix('h')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('certificate_received')
                    ->label('Attestation')
                    ->boolean()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_closed')
                    ->label('Cloture')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('training_type')
                    ->label('Type')
                    ->options([
                        'type1' => 'Type 1',
                        'type2' => 'Type 2',
                        'type3' => 'Type 3',
                    ]),
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
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
