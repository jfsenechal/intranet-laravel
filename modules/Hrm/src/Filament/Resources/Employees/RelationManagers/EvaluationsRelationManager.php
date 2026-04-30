<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Evaluations\Tables\EvaluationTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class EvaluationsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'evaluations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Evaluations';
    }

    public function isReadOnly22(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return EvaluationTables::configure($table);
    }
}
