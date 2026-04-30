<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\Pages;

use AcMarche\MailingList\Filament\Resources\AddressBooks\AddressBookResource;
use AcMarche\MailingList\Models\AddressBookShare;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewAddressBook extends ViewRecord
{
    #[Override]
    protected static string $resource = AddressBookResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
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
                    ->state(function () {
                        $usernames = AddressBookShare::query()
                            ->where('address_book_id', $this->record->id)
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

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
