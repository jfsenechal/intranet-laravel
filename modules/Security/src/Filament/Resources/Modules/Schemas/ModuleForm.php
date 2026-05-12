<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Modules\Schemas;

use AcMarche\Security\Models\Module;
use AcMarche\Security\Repository\ModuleRepository;
use AcMarche\Security\Repository\RoleRepository;
use AcMarche\Security\Repository\UserRepository;
use App\Models\User;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

final class ModuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
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
                Checkbox::make('allow_multiple_roles')
                    ->label('Allow multiple roles')
                    ->helperText('Allow users to have multiple roles for this module'),
                ColorPicker::make('color')
                    ->label('Couleur')
                    ->required(),
                TextInput::make('icon')
                    ->required()
                    ->maxLength(255),
                TextInput::make('description')
                    ->maxLength(255)
                    ->columnSpanFull(),
                TextInput::make('role_description')
                    ->maxLength(255)
                    ->label('Description de l\'attribution des rôles')
                    ->helperText('Quelques explications sur l\'attribution des roles')
                    ->columnSpanFull(),
            ]);
    }

    public static function addUserFromModule(Schema $schema, Model|Module $module): Schema
    {
        $user = $schema->getRecord(); // if new null value, if edit user instance
        $components = [];

        if (! $user?->id > 0) {
            $components[] = Select::make('user')
                ->label('Agent')
                ->options(fn (UserRepository $repository): array => $repository->listLocalUsersForSelect())
                ->searchable()
                ->columnSpanFull();
        }

        $components[] = self::rolesField($module, $user);

        $schema->components($components);

        return $schema;
    }

    public static function addModuleFromUser(Schema $schema, User|Model $user): Schema
    {
        /**
         * @var Module|null $module
         */
        $module = $schema->getRecord(); // if new null if edit module instance

        $components = [];
        if (! $module?->id > 0) {
            $components[] =
                Select::make('module')
                    ->label('Module')
                    ->options(fn (ModuleRepository $repository): array => $repository->getModulesForSelect())
                    ->reactive()
                    ->afterStateUpdated(function (Set $set): void {
                        // Optional: clear roles selection when module changes
                        $set('roles', []);
                    })
                    ->columnSpanFull();
        }

        $components[] = self::rolesField($module, $user);

        $schema
            ->schema($components)
            ->columns(1);

        return $schema;
    }

    public static function rolesField(?Module $module, User|Model|null $user = null): CheckboxList|Radio
    {
        $options = function (callable $get) use ($module) {
            if (! $module instanceof Module) {
                $moduleId = $get('module');
                if (! $moduleId) {
                    return [];
                }
                $module = ModuleRepository::find($moduleId);
            }
            [$rolesName, $rolesDescription] = RoleRepository::getForSelect($module);

            return $rolesName;
        };

        $descriptions = function (callable $get) use ($module) {
            if (! $module instanceof Module) {
                $moduleId = $get('module');
                if (! $moduleId) {
                    return [];
                }
                $module = ModuleRepository::find($moduleId);
            }
            [$rolesName, $rolesDescription] = RoleRepository::getForSelect($module);

            return $rolesDescription;
        };

        $userRoles = ($user && $module)
            ? $user->rolesByModule($module->id)->pluck('name')
            : collect();

        if ($module?->allow_multiple_roles) {

            return CheckboxList::make('roles')
                ->label('Rôles')
                ->options($options)
                ->descriptions($descriptions)
                ->columnSpanFull()
                ->afterStateHydrated(fn (CheckboxList $component): CheckboxList => $component->state($userRoles->toArray()));
        }

        return Radio::make('roles')
            ->label('Rôle')
            ->options($options)
            ->descriptions($descriptions)
            ->afterStateHydrated(fn (Radio $component): Radio => $component->state($userRoles->first()));
    }
}
