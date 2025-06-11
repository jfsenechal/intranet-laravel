<?php

namespace AcMarche\Security\Form;

use AcMarche\Security\Models\Module;
use AcMarche\Security\Repository\ModuleRepository;
use AcMarche\Security\Repository\RoleRepository;
use AcMarche\Security\Repository\UserRepository;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class ModuleForm
{
    public static function createForm(Schema $form): Schema
    {
        return $form
            ->columns(2)
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(120),
                TextInput::make('url')
                    ->label('Url')
                    ->required()
                    ->maxLength(255),
                Checkbox::make('is_public')
                    ->label('Publique')
                    ->helperText('Accessible à tous'),
                Checkbox::make('is_external')
                    ->label('Externe')
                    ->helperText('Url externe'),
                ColorPicker::make('color')
                    ->label('Couleur')
                    ->required(),
                TextInput::make('icon')
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function addUserFromModule(Schema $form, Model|Module $module): Schema
    {
        $user = $form->getRecord();//if new null value, if edit user instance
        $schema = [];

        if (!$user?->id > 0) {
            $schema[] = Forms\Components\Select::make('user')
                ->label('Utilisateur')
                ->options(fn(UserRepository $repository): array => $repository->getUsersForSelect())
                ->columnSpanFull();
        }

        $schema[] = self::rolesField($module);

        $form->schema($schema);

        return $form;
    }

    public static function addModuleFromUser(Schema $form, User|Model $user): Schema
    {
        /**
         * @var Module|null $module
         */
        $module = $form->getRecord();//if new null if edit module instance

        $schema = [];
        if (!$module?->id > 0) {
            $schema[] =
                Forms\Components\Select::make('module')
                    ->label('Module')
                    ->options(fn(ModuleRepository $repository) => $repository->getModulesForSelect())
                    ->reactive()
                    ->afterStateUpdated(function (Set $set) {
                        // Optional: clear roles selection when module changes
                        $set('roles', []);
                    })
                    ->columnSpanFull();
        }

        $schema[] = self::rolesField($module);

        $form->schema($schema);

        return $form;
    }

    private static function rolesField(?Module $module): CheckboxList
    {
        return CheckboxList::make('roles')
            ->label('Rôles')
            ->options(function (callable $get) use ($module) {
                if (!$module) {
                    $moduleId = $get('module');
                    if (!$moduleId) {
                        return [];
                    }
                    $module = ModuleRepository::find($moduleId);
                }
                [$rolesName, $rolesDescription] = RoleRepository::getForSelect($module);

                return $rolesName;
            })
            ->descriptions(function (callable $get) use ($module) {
                if (!$module) {
                    $moduleId = $get('module');
                    if (!$moduleId) {
                        return [];
                    }
                    $module = ModuleRepository::find($moduleId);
                }
                [$rolesName, $rolesDescription] = RoleRepository::getForSelect($module);

                return $rolesDescription;
            });
    }
}
