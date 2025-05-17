<?php

namespace AcMarche\Security\Filament\Resources\UserResource\RelationManagers;

use AcMarche\Security\Form\ModuleForm;
use AcMarche\Security\Tables\ModuleTables;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
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

    public function form(Form $form): Form
    {
        return ModuleForm::addModuleFromUser($form, $this->ownerRecord);
    }

}
