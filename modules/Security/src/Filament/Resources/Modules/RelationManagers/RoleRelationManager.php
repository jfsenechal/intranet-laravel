<?php

namespace AcMarche\Security\Filament\Resources\Modules\RelationManagers;

use AcMarche\Security\Filament\Resources\Users\Schemas\RoleForm;
use AcMarche\Security\Filament\Resources\Users\Tables\RoleTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RoleRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return ' Rôles';
    }

    public function isReadOnly(): bool
    {
        return false;
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
