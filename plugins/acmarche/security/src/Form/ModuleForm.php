<?php

namespace AcMarche\Security\Form;

use AcMarche\Security\Models\Module;
use AcMarche\Security\Repository\ModuleRepository;
use AcMarche\Security\Repository\RoleRepository;
use AcMarche\Security\Repository\UserRepository;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Database\Eloquent\Model;

class ModuleForm
{
    public static function createForm(Form $form): Form
    {
        return $form
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(120),
                Forms\Components\TextInput::make('url')
                    ->label('Url')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Checkbox::make('is_public')
                    ->label('Publique')
                    ->helperText('Accessible à tous'),
                Forms\Components\Checkbox::make('is_external')
                    ->label('Externe')
                    ->helperText('Url externe'),
                Forms\Components\ColorPicker::make('color')
                    ->label('Couleur')
                    ->required(),
                Forms\Components\TextInput::make('icon')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function addUserFromModule(Form $form, Model|Module $module): Form
    {
        $user = $form->getRecord();
        //dd($user?->name);
        $roles = RoleRepository::getForSelect($module);
        $rolesName = $roles[0];
        $rolesDescription = $roles[1];
        $schema = [];

        //   if (!$user?->id) {
        $schema[] = Forms\Components\Select::make('user')
            ->label('Utilisateur')
            ->options(fn(UserRepository $repository): array => $repository->getUsersForSelect())
            ->columnSpanFull();
        // }

        $schema[] = Forms\Components\CheckboxList::make('roles')
            ->label('Rôles')
            ->options($rolesName)
            ->descriptions($rolesDescription);

        return $form->schema($schema);
    }

    public static function addModuleFromUser(Form $form, Model|Module $user): Form
    {
        //$user = $form->getRecord();
        return $form->schema([
            Forms\Components\Select::make('module')
                ->label('Module')
                ->options(fn(ModuleRepository $repository) => $repository->getModulesForSelect())
                ->reactive()
                ->afterStateUpdated(function (callable $set, $state, callable $get) {
                    // Optional: clear roles selection when module changes
                    $set('roles', []);
                })
                ->columnSpanFull(),

            Forms\Components\CheckboxList::make('roles')
                ->label('Rôles')
                ->options(function (callable $get) {
                    $moduleId = $get('module');
                    if (!$moduleId) {
                        return [];
                    }
                    $module = ModuleRepository::find($moduleId);
                    [$rolesName, $rolesDescription] = RoleRepository::getForSelect($module);

                    return $rolesName;
                })
                ->descriptions(function (callable $get) {
                    $moduleId = $get('module');
                    if (!$moduleId) {
                        return [];
                    }
                    $module = ModuleRepository::find($moduleId);
                    [$rolesName, $rolesDescription] = RoleRepository::getForSelect($module);

                    return $rolesDescription;
                }),
        ]);
    }
}
