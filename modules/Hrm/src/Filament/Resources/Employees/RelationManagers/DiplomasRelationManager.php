<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Diplomas\Tables\DiplomaTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class DiplomasRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'diplomas';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Diplômes';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return DiplomaTables::relation($table);
    }
}
