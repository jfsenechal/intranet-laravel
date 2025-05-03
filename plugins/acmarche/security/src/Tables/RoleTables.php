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
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('module.name')
                    ->label('Module')
                    ->sortable()
                    ->searchable(),
            ]);
    }
}
