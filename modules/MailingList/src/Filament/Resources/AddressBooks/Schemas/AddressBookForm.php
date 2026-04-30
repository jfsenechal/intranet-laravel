<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\Schemas;

use AcMarche\MailingList\Filament\Resources\Contacts\Schemas\ContactForm;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use AcMarche\MailingList\Repositories\AddressBookRepository;
use AcMarche\MailingList\Shares\ShareHandler;
use AcMarche\Security\Repository\UserRepository;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
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
                Hidden::make('username')
                    ->default(fn (): ?string => auth()->user()?->username),
                Select::make('contacts')
                    ->relationship('contacts', 'email')
                    ->getOptionLabelFromRecordUsing(
                        fn (Contact $record): string => "{$record->first_name} {$record->last_name} ({$record->email})"
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->createOptionForm([
                        Grid::make(2)->schema(
                            ContactForm::columns()
                        ),
                    ])
                    ->createOptionUsing(fn (array $data): int => Contact::query()->create([
                        ...$data,
                        'username' => auth()->user()?->username,
                    ])->getKey()),
                Select::make('sharedWithUsers')
                    ->label('Partager avec')
                    ->options(
                        fn (): array => UserRepository::listLocalUsersForSelect()
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
