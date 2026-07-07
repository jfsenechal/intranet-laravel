<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Archive\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ArchiveTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sent_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('name')
                    ->label('Intitulé')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('sent_at')
                    ->label('Date d\'envoi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
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
