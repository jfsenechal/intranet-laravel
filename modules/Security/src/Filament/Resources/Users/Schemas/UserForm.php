<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Users\Schemas;

use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                Toggle::make('news_attachment')
                    ->label('Pièce jointe news')
                    ->helperText('L\'agent recevra les pièces jointes de la news par mail')
                    ->columnSpanFull(),
                Toggle::make('is_administrator')
                    ->label('Administrateur')
                    ->helperText('<!>Attention super administrator de l\'intranet. Accès total en écriture à toutes les données ! <!>')
                    ->columnSpanFull(),
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
            ]);
    }
}
