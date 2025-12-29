<?php

namespace AcMarche\Hrm\Filament\Resources\Directions\Tables;

use AcMarche\Hrm\Filament\Resources\Directions\DirectionResource;
use AcMarche\Hrm\Models\Direction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;

final class DirectionTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('title')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Intitule')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Direction $record) => DirectionResource::getUrl('view', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('abbreviation')
                    ->label('Abreviation')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('director')
                    ->label('Directeur')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('employer.name')
                    ->label('Employeur')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('services_count')
                    ->label('Services')
                    ->counts('services')
                    ->sortable(),
            ])
            ->filters([])
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
