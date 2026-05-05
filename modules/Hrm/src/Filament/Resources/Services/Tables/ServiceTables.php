<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Services\Tables;

use AcMarche\Hrm\Filament\Filters\DirectionFilter;
use AcMarche\Hrm\Filament\Resources\Services\ServiceResource;
use AcMarche\Hrm\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->label('Intitule')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('abbreviation')
                    ->label('Abréviation')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('direction.name')
                    ->label('Direction')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Telephone')
                    ->toggleable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                DirectionFilter::make(),
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
