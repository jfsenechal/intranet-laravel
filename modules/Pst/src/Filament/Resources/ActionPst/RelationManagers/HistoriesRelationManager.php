<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class HistoriesRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'histories';

    #[Override]
    protected static ?string $title = 'Historique';

    #[Override]
    protected static ?string $label = 'Historique';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Textarea::make('body')
                    ->label('Contenu')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('property')
            ->columns([
                TextColumn::make('body')
                    ->label('Changement')
                    ->html()
                    ->limit(120)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
                TextColumn::make('user_add')
                    ->label('Ajouté par'),
                TextColumn::make('created_at')
                    ->label('Modifié le')
                    ->dateTime(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->recordActions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
