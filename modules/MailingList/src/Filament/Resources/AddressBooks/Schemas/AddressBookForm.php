<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\Schemas;

use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Repositories\AddressBookRepository;
use AcMarche\MailingList\Shares\ShareHandler;
use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

final class AddressBookForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->maxLength(255)
                    ->required(),
                Toggle::make('is_public')
                    ->label('Public')
                    ->helperText('Visible en lecture seule par tous les utilisateurs.')
                    ->default(false),
                Hidden::make('username')
                    ->default(fn (): ?string => auth()->user()?->username),
                Select::make('sharedWithUsers')
                    ->label('Partager avec')
                    ->options(
                        fn (): array => UserRepository::listLocalUsersForSelectByUsername()
                    )
                    ->multiple()
                    ->searchable()
                    ->afterStateHydrated(function (Select $component, ?AddressBook $record): void {
                        if ($record instanceof AddressBook) {
                            $component->state(
                                AddressBookRepository::getSharingAddressBookByAddressBook($record)
                            );
                        }
                    })
                    ->saveRelationshipsUsing(function (AddressBook $record, ?array $state): void {
                        ShareHandler::syncSharing($record, $state);
                    })
                    ->dehydrated(false),
            ]);
    }
}
