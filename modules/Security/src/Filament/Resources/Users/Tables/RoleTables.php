<?php

namespace AcMarche\Security\Filament\Resources\Users\Tables;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

final class RoleTables
{
    public static function inline(Table $table): Table
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
                CreateAction::make('create'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
