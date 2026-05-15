<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groupes\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class InscriptionsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'inscriptions';

    #[Override]
    protected static ?string $title = 'Inscriptions';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sportif.nom')->label('Nom')->sortable()->searchable(),
                TextColumn::make('sportif.prenom')->label('Prénom')->sortable()->searchable(),
                TextColumn::make('sportif.localite')->label('Localité'),
                TextColumn::make('prix')->label('Prix')->money('EUR'),
                TextColumn::make('created_at')->label('Inscription')->date(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la sélection')
                        ->icon(Heroicon::Trash),
                ]),
            ]);
    }
}
