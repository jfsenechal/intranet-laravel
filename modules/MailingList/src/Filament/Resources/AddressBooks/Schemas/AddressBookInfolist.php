<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\Schemas;

use AcMarche\Security\Repository\UserRepository;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

/**
 * The name is already the page title; contacts and shares are shown by the
 * relation managers below the infolist.
 */
final class AddressBookInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('username')
                    ->label('Propriétaire')
                    ->formatStateUsing(fn (string $state): string => UserRepository::findByUsername($state)?->full_name ?? $state),
                IconEntry::make('is_public')
                    ->label('Public')
                    ->helperText('Visible en lecture seule par tous les utilisateurs.')
                    ->boolean(),
            ]);
    }
}
