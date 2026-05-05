<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Recipients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class RecipientTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('last_name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('username')
                    ->label('Utilisateur')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('supervisor.full_name')
                    ->label('Superviseur')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('receives_attachments')
                    ->label('PJ')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
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
