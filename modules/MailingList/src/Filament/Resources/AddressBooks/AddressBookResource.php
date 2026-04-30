<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks;

use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\CreateAddressBook;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\EditAddressBook;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\ListAddressBooks;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\ViewAddressBook;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Schemas\AddressBookForm;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Tables\AddressBooksTable;
use AcMarche\MailingList\Models\AddressBook;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class AddressBookResource extends Resource
{
    #[Override]
    protected static ?string $model = AddressBook::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Carnets d\'adresses';

    #[Override]
    protected static ?string $modelLabel = 'carnet d\'adresses';

    #[Override]
    protected static ?string $pluralModelLabel = 'carnets d\'adresses';

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'name',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return AddressBookForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AddressBooksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAddressBooks::route('/'),
            'create' => CreateAddressBook::route('/create'),
            'view' => ViewAddressBook::route('/{record}'),
            'edit' => EditAddressBook::route('/{record}/edit'),
        ];
    }
}
