<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Recipients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class RecipientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
            ])
            ->filters([
                SelectFilter::make('groups')
                    ->label('Groupe')
                    ->relationship('groups', 'name'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Voir')
                    ->icon(Heroicon::Eye),
                EditAction::make()
                    ->label('Modifier')
                    ->icon(Heroicon::PencilSquare),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la selection')
                        ->icon(Heroicon::Trash),
                ]),
            ]);
    }
}
