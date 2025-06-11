<?php

namespace AcMarche\Security\Filament\Resources\UserResource\RelationManagers;

use AcMarche\Security\Form\ModuleForm;
use AcMarche\Security\Tables\ModuleTables;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ModuleRelationManager extends RelationManager
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
