<?php

namespace AcMarche\Hrm\Filament\Resources\Contracts\Tables;

use AcMarche\Hrm\Filament\Resources\Contracts\ContractResource;
use AcMarche\Hrm\Models\Contract;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class ContractTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('employee.last_name')
                    ->label('Agent')
                    ->formatStateUsing(fn (Contract $record) => $record->employee->last_name.' '.$record->employee->first_name)
                    ->searchable(['last_name', 'first_name'])
                    ->sortable()
                    ->url(fn (Contract $record) => ContractResource::getUrl('view', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('employer.name')
                    ->label('Employeur')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('contractType.name')
                    ->label('Type')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('job_title')
                    ->label('Fonction')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Debut')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Fin')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_regime')
                    ->label('Regime')
                    ->suffix('%')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_closed')
                    ->label('Cloture')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('employer_id')
                    ->label('Employeur')
                    ->relationship('employer', 'name'),
                SelectFilter::make('contract_type_id')
                    ->label('Type')
                    ->relationship('contractType', 'name'),
                TernaryFilter::make('is_closed')
                    ->label('Cloture')
                    ->placeholder('Tous')
                    ->trueLabel('Clotures')
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
