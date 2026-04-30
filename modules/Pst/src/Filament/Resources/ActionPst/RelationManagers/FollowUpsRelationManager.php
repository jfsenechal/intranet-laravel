<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\RelationManagers;

use AcMarche\Pst\Filament\Resources\FollowUp\Tables\FollowUpTables;
use Filament\Forms\Components\RichEditor;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class FollowUpsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'followups';

    #[Override]
    protected static ?string $title = 'Suivi';

    #[Override]
    protected static ?string $label = 'Suivi';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                RichEditor::make('content')
                    ->label('Contenu')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return FollowUpTables::configure($table);
    }
}
