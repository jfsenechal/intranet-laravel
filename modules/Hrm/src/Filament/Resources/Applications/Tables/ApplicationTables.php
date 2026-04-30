<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Applications\Tables;

use AcMarche\Hrm\Models\Application;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class ApplicationTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('received_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('employee.last_name')
                    ->label('Candidat')
                    ->formatStateUsing(
                        fn (Application $record
                        ): string => $record->employee->last_name.' '.$record->employee->first_name
                    )
                    ->searchable(['last_name', 'first_name'])
                    ->sortable(),
                TextColumn::make('received_at')
                    ->label('Reçue le')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('employer.name')
                    ->label('Employeur')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('jobFunction.name')
                    ->label('Fonction')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('public_call')
                    ->label('Appel public')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('mail_reference')
                    ->label('Référence')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_spontaneous')
                    ->label('Spontanée')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('is_public_call')
                    ->label('Appel public')
                    ->boolean()
                    ->toggleable(),
                IconColumn::make('is_priority')
                    ->label('Prioritaire')
                    ->boolean()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('employer')
                    ->label('Employeur')
                    ->relationship('employer', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('jobFunction')
                    ->label('Fonction')
                    ->relationship('jobFunction', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('is_spontaneous')
                    ->label('Spontanée')
                    ->placeholder('Toutes')
                    ->trueLabel('Oui')
                    ->falseLabel('Non'),
                TernaryFilter::make('is_public_call')
                    ->label('Appel public')
                    ->placeholder('Toutes')
                    ->trueLabel('Oui')
                    ->falseLabel('Non'),
                TernaryFilter::make('is_priority')
                    ->label('Prioritaire')
                    ->placeholder('Toutes')
                    ->trueLabel('Oui')
                    ->falseLabel('Non'),
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
