<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Applications\Tables\ApplicationTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class ApplicationsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'applications';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Candidatures';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return ApplicationTables::configure($table);
    }
}
