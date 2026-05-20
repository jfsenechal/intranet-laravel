<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activities\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class RegistrationsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'registrations';

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
                TextColumn::make('member.last_name')->label('Nom')->sortable()->searchable(),
                TextColumn::make('member.first_name')->label('Prénom')->sortable()->searchable(),
                TextColumn::make('group.day')->label('Jour'),
                TextColumn::make('group.location')->label('Lieu'),
                TextColumn::make('price')->label('Prix')->money('EUR'),
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
