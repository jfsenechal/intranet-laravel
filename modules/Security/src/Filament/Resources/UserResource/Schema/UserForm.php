<?php

namespace AcMarche\Security\Filament\Resources\UserResource\Schema;

use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([]);
    }

    public static function add(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('username')
                    ->label('Nom')
                    ->options(UserRepository::listUsersFromLdapForSelect())
                    ->searchable(),
            ]);
    }
}
