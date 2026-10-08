<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\Schemas;

use AcMarche\MailingList\Models\AddressBookShare;
use App\Models\User;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class AddressBookInfolist
{
    public static function configure(Schema $schema, int $recordId): Schema
    {
        return $schema
            ->components([
                RepeatableEntry::make('contacts')
                    ->schema([
                        TextEntry::make('first_name')->label('Prénom'),
                        TextEntry::make('last_name')->label('Nom'),
                        TextEntry::make('email'),
                        TextEntry::make('phone')->label('Téléphone'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),
                RepeatableEntry::make('sharedUsers')
                    ->label('Partagé avec')
                    ->state(function () use ($recordId) {
                        $usernames = AddressBookShare::query()
                            ->where('address_book_id', $recordId)
                            ->pluck('username');

                        return User::query()
                            ->whereIn('username', $usernames)
                            ->get();
                    })
                    ->schema([
                        TextEntry::make('name'),
                        TextEntry::make('email'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
