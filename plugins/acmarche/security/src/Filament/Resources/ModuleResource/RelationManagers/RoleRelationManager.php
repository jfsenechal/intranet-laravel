<?php

namespace AcMarche\Security\Filament\Resources\ModuleResource\RelationManagers;

use AcMarche\Security\Tables\RoleTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RoleRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return ' Rôles';
    }

    public function table(Table $table): Table
    {
        return RoleTables::inline($table);
    }

}
