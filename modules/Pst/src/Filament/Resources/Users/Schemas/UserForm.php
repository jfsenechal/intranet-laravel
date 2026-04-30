<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Users\Schemas;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Models\Service;
use AcMarche\Security\Models\Role;
use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                CheckboxList::make('user_roles')
                    ->label('Rôles')
                    ->options(fn () => Role::pluck('name', 'id'))
                    ->dehydrated(false),
                ToggleButtons::make('departments')
                    ->label('Département(s)')
                    ->default(DepartmentEnum::VILLE->value)
                    ->options(
                        [
                            DepartmentEnum::VILLE->value => DepartmentEnum::VILLE->getLabel(),
                            DepartmentEnum::CPAS->value => DepartmentEnum::CPAS->getLabel(),
                        ]
                    )
                    ->multiple()
                    ->required(),
                CheckboxList::make('user_services')
                    ->label('Services')
                    ->options(fn () => Service::pluck('name', 'id'))
                    ->dehydrated(false)
                    ->columns(2),
            ]);
    }

    public static function add(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('username')
                    ->label('Nom')
                    ->options(UserRepository::listLdapUsersForSelect())
                    ->searchable(),
                ToggleButtons::make('departments')
                    ->label('Département(s)')
                    ->default(DepartmentEnum::VILLE->value)
                    ->options(
                        [
                            DepartmentEnum::VILLE->value => DepartmentEnum::VILLE->getLabel(),
                            DepartmentEnum::CPAS->value => DepartmentEnum::CPAS->getLabel(),
                        ]
                    )
                    ->multiple()
                    ->required(),
                CheckboxList::make('user_roles')
                    ->label('Rôles')
                    ->options(fn () => Role::pluck('name', 'id')),
            ]);
    }
}
