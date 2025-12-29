<?php

namespace AcMarche\Security\Filament\Resources\Modules\RelationManagers;

use AcMarche\Security\Filament\Resources\Modules\Schemas\ModuleForm;
use AcMarche\Security\Filament\Resources\Users\Tables\UserTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return ' Utilisateurs';
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return UserTables::inline($table, $this->ownerRecord);
    }

    public function form(Schema $schema): Schema
    {
        return ModuleForm::addUserFromModule($schema, $this->ownerRecord);
    }
}
