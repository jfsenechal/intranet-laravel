<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\StrategicObjective\RelationManagers;

use AcMarche\Pst\Filament\Resources\OperationalObjective\Tables\OperationalObjectiveTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class OosRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'oos';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return $ownerRecord->oos()->count().' Objectifs Opérationnels (OO)';
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return OperationalObjectiveTables::tableInline($table);
    }
}
