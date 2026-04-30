<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Services\Tables;

use AcMarche\Courrier\Filament\Resources\Services\ServiceResource;
use AcMarche\Courrier\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ServiceTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Service $record): string => ServiceResource::getUrl('view', ['record' => $record->id])),
                TextColumn::make('initials')
                    ->label('Initiales')
                    ->searchable(),
                TextColumn::make('recipients_count')
                    ->label('Destinataires')
                    ->counts('recipients')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('department.name')
                    ->label('Département')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
