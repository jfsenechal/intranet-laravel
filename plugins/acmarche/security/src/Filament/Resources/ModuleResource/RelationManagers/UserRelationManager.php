<?php

namespace AcMarche\Security\Filament\Resources\ModuleResource\RelationManagers;

use AcMarche\Security\Form\ModuleForm;
use AcMarche\Security\Tables\UserTables;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected function getTableQuery(): Builder
    {
        $module = $this->getOwnerRecord();

        return User::query()
            ->whereHas('roles', fn(Builder $query) => $query->where('module_id', $module->getKey()));
    }

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

    public function form(Form $form): Form
    {
        $form->model($this->ownerRecord);

        return ModuleForm::userForm($form, $this->ownerRecord);
    }

}
