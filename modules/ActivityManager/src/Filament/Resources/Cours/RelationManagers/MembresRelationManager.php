<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Cours\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class MembresRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'membres';

    #[Override]
    protected static ?string $title = 'Inscrits';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom')
            ->columns([
                TextColumn::make('nom')->label('Nom')->searchable()->sortable(),
                TextColumn::make('prenom')->label('Prénom')->searchable()->sortable(),
                TextColumn::make('civilite')->label('Civilité')->badge()->toggleable(),
                TextColumn::make('email')->label('Email')->copyable()->toggleable(),
                IconColumn::make('enabled')->label('Actif')->boolean(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Inscrire un membre')
                    ->icon(Heroicon::Plus)
                    ->preloadRecordSelect(),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Désinscrire')
                    ->icon(Heroicon::XMark),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Désinscrire la sélection')
                        ->icon(Heroicon::XMark),
                ]),
            ]);
    }
}
