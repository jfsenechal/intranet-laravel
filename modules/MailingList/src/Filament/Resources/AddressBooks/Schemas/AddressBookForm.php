<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\Schemas;

use AcMarche\MailingList\Models\AddressBook;
use Filament\Forms\Components\Hidden;
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
                    ->default(false)
                    ->disabled(fn (?AddressBook $record): bool => $record instanceof AddressBook && ! $record->isOwnedBy(auth()->user())),
                Hidden::make('username')
                    ->default(fn (): ?string => auth()->user()?->username),
            ]);
    }
}
