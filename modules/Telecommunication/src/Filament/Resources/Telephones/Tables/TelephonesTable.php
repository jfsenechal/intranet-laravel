<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\Telephones\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class TelephonesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_name')
                    ->label('Utilisateur')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('number')
                    ->label('Numéro')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('lineType.name')
                    ->label('Type de ligne')
                    ->sortable()
                    ->badge(),
                TextColumn::make('service')
                    ->label('Service')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('department')
                    ->label('Département')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('location')
                    ->label('Localisation')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('archived')
                    ->label('Archivé')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Mise à jour')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('line_type_id')
                    ->label('Type de ligne')
                    ->relationship('lineType', 'name'),
                TernaryFilter::make('archived')
                    ->label('Archivé')
                    ->placeholder('Tous')
                    ->trueLabel('Archivés')
                    ->falseLabel('Actifs')
                    ->default(false),
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
