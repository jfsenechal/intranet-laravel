<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Members\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class ActivitiesRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'cours';

    #[Override]
    protected static ?string $title = 'Cours suivis';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom')
            ->columns([
                TextColumn::make('nom')->label('Nom')->searchable()->sortable()->limit(80)->wrap(),
                TextColumn::make('activite.nom')->label('Activité')->badge()->toggleable(),
                TextColumn::make('date_debut')->label('Début')->date('d/m/Y')->sortable(),
                TextColumn::make('date_fin')->label('Fin')->date('d/m/Y')->sortable()->placeholder('—'),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Inscrire à un cours')
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
