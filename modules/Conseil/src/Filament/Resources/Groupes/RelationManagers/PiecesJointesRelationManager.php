<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Groupes\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class PiecesJointesRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'piecesJointes';

    #[Override]
    protected static ?string $title = 'Pièces jointes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nom')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->label('Description')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nom')
            ->columns([
                TextColumn::make('nom')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->placeholder('—')
                    ->searchable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Ajouter une pièce jointe')
                    ->icon(Heroicon::Plus),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Supprimer')
                    ->icon(Heroicon::Trash),
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
