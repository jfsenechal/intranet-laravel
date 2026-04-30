<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\RelationManagers;

use AcMarche\Hrm\Filament\Resources\Valorizations\Tables\ValorizationTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class ValorizationsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'valorizations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Valorisations';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return ValorizationTables::relation($table);
    }
}
