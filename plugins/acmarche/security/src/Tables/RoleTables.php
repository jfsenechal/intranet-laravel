<?php

namespace AcMarche\Security\Tables;

use Filament\Tables;
use Filament\Tables\Table;

class RoleTables
{
    public static function inline($table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nom'),
                Tables\Columns\TextColumn::make('description')
                    ->label('Description'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('create'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
