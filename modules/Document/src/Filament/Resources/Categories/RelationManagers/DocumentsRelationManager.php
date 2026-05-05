<?php

declare(strict_types=1);

namespace AcMarche\Document\Filament\Resources\Categories\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Override;

final class DocumentsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'documents';

    #[Override]
    protected static ?string $title = 'Documents';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Intitulé')
                    ->searchable(),
            ])
            ->filters([])
            ->recordActions([
                ViewAction::make(),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([]);
    }
}
