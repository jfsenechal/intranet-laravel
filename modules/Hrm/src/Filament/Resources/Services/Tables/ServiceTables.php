<?php

namespace AcMarche\Hrm\Filament\Resources\Services\Tables;

use AcMarche\Hrm\Filament\Resources\Services\ServiceResource;
use AcMarche\Hrm\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class ServiceTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('title')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Intitule')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Service $record) => ServiceResource::getUrl('view', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('abbreviation')
                    ->label('Abreviation')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('direction.title')
                    ->label('Direction')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Telephone')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('direction_id')
                    ->label('Direction')
                    ->relationship('direction', 'title'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
