<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class ProfileTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('last_name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom'),
                TextColumn::make('first_name')
                    ->label('Prénom'),
                TextColumn::make('username')
                    ->label('Identifiant Ldap')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->copyable(),
                TextColumn::make('employee_id')
                    ->label('Identifiant Grh')
                    ->badge(),
                TextColumn::make('location')
                    ->label('Emplacement')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(30),
                TextColumn::make('external_applications_count')
                    ->counts('externalApplications')
                    ->label('Applications')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('folders_count')
                    ->counts('folders')
                    ->label('Dossiers')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('no_mail')
                    ->label('Sans mail')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('no_mail')->label('Sans mailbox'),
                TernaryFilter::make('employee_id')->label('Sans employee_id'),
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
