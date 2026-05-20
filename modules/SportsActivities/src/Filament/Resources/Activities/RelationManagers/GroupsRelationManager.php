<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Activities\RelationManagers;

use AcMarche\SportsActivities\Filament\Resources\Groups\Schemas\GroupForm;
use AcMarche\SportsActivities\Filament\Resources\Groups\Tables\GroupsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class GroupsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'groups';

    #[Override]
    protected static ?string $title = 'Groupes';

    public function form(Schema $schema): Schema
    {
        return GroupForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return GroupsTable::configure($table);
    }

    #[Override]
    public function isReadOnly(): bool
    {
        return false;
    }
}
