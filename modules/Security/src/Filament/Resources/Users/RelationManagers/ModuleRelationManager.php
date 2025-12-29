<?php

namespace AcMarche\Security\Filament\Resources\Users\RelationManagers;

use AcMarche\Security\Filament\Resources\Modules\Schemas\ModuleForm;
use AcMarche\Security\Filament\Resources\Modules\Tables\ModuleTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ModuleRelationManager extends RelationManager
{
    protected static string $relationship = 'modules';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return ' Modules';
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return ModuleTables::inline($table, $this->ownerRecord);
    }

    public function form(Schema $schema): Schema
    {
        return ModuleForm::addModuleFromUser($schema, $this->ownerRecord);
    }
}
