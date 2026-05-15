<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Destinataires\RelationManagers;

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

final class GroupesRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'groupes';

    #[Override]
    protected static ?string $title = 'Groupes';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom')
            ->columns([
                TextColumn::make('nom')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Attacher un groupe')
                    ->icon(Heroicon::Plus),
            ])
            ->recordActions([
                DetachAction::make()
                    ->label('Détacher')
                    ->icon(Heroicon::Trash),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->label('Détacher la sélection')
                        ->icon(Heroicon::Trash),
                ]),
            ]);
    }
}
