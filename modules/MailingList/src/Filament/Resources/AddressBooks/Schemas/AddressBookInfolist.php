<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\Schemas;

use Filament\Infolists\Components\IconEntry;
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
            ->components([
                IconEntry::make('is_public')
                    ->label('Publique')
                    ->helperText('Visible en lecture seule par tous les utilisateurs.')
                    ->boolean(),
            ]);
    }
}
