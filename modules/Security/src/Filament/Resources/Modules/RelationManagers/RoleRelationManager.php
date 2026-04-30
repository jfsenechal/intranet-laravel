<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Modules\RelationManagers;

use AcMarche\Security\Filament\Resources\Users\Schemas\RoleForm;
use AcMarche\Security\Filament\Resources\Users\Tables\RoleTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Override;

final class RoleRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'roles';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return ' Rôles';
    }

    public function isReadOnly(): bool
    {
        return ! auth()->user()->isAdministrator();
    }

    public function table(Table $table): Table
    {
        return RoleTables::inline($table);
    }

    public function form(Schema $schema): Schema
    {
        return RoleForm::configure($schema);
    }
}
