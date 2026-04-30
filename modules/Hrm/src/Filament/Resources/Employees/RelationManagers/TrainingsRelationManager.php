<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Trainings\Tables\TrainingTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class TrainingsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'trainings';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Formations';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return TrainingTables::relation($table);
    }
}
