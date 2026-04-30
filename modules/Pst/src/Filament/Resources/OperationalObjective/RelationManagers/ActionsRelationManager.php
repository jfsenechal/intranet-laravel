<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\OperationalObjective\RelationManagers;

use AcMarche\Pst\Filament\Resources\ActionPst\Tables\ActionTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class ActionsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'actionsForDepartment';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return $ownerRecord->actionsForDepartment()->count().' Actions liées';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return ActionTables::actionsInline($table);
    }
}
