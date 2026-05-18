<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Destinataires\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

final class DestinatairesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nom')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('prenom')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),
                IconColumn::make('pv_service')
                    ->label('PV Service')
                    ->boolean(),
                IconColumn::make('ordre_service')
                    ->label('OJ Service')
                    ->boolean(),
                IconColumn::make('ordre_college')
                    ->label('OJ Collège')
                    ->boolean(),
                IconColumn::make('pv_college')
                    ->label('PV Collège')
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('pv_service')->label('PV Service'),
                TernaryFilter::make('ordre_service')->label('Ordre du jour - Service'),
                TernaryFilter::make('ordre_college')->label('Ordre du jour - Collège'),
                TernaryFilter::make('pv_college')->label('PV Collège'),
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
