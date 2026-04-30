<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Users\Tables;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class RoleTables
{
    public static function inline(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom'),
                TextColumn::make('description')
                    ->label('Description'),
            ])
            ->headerActions([
                CreateAction::make('create')
                    ->label('Ajouter un rôle')
                    ->icon(Heroicon::PlusCircle),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
