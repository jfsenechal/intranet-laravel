<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Categories\RelationManagers;

use AcMarche\CpasLibrary\Filament\Resources\Fiches\FicheResource;
use AcMarche\CpasLibrary\Models\Fiche;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class FichesRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'fiches';

    #[Override]
    protected static ?string $title = 'Fiches';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->limit(80),
                TextColumn::make('userAdd')
                    ->label('Auteur')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('createdAt')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Voir')
                    ->icon(Heroicon::Eye)
                    ->url(fn (Fiche $record): string => FicheResource::getUrl('view', ['record' => $record])),
                EditAction::make()
                    ->label('Modifier')
                    ->icon(Heroicon::PencilSquare),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la sélection')
                        ->icon(Heroicon::Trash),
                ]),
            ]);
    }
}
