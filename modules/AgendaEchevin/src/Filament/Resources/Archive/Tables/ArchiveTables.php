<?php

declare(strict_types=1);

namespace AcMarche\AgendaEchevin\Filament\Resources\Archive\Tables;

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
                TextColumn::make('title')
                    ->label('Intitulé')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('sent_at')
                    ->label('Date d\'envoi')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('recipients')
                    ->label('Destinataires')
                    ->limit(80),
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
