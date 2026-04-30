<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Internships\Tables\InternshipTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class InternshipsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'internships';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Stages';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return InternshipTables::relation($table);
    }
}
