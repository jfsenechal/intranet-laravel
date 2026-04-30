<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\RelationManagers;

use AcMarche\Pst\Filament\Resources\ActionPst\Schemas\MediaForm;
use AcMarche\Pst\Filament\Resources\ActionPst\Tables\MediaTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class MediasRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'medias';

    public function form(Schema $schema): Schema
    {
        return MediaForm::configure($schema);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return MediaTables::configure($table);
    }
}
