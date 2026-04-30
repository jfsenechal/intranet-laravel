<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contacts;

use AcMarche\Hrm\Filament\Resources\Contacts\Pages\CreateContact;
use AcMarche\Hrm\Filament\Resources\Contacts\Pages\EditContact;
use AcMarche\Hrm\Filament\Resources\Contacts\Pages\ListContacts;
use AcMarche\Hrm\Filament\Resources\Contacts\Schemas\ContactForm;
use AcMarche\Hrm\Filament\Resources\Contacts\Tables\ContactsTable;
use AcMarche\Hrm\Models\Contact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class ContactResource extends Resource
{
    #[Override]
    protected static ?string $model = Contact::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Listing';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Contacts';

    #[Override]
    protected static ?string $modelLabel = 'contact';

    #[Override]
    protected static ?string $pluralModelLabel = 'contacts';

    #[Override]
    protected static ?string $recordTitleAttribute = 'last_name';

    public static function form(Schema $schema): Schema
    {
        return ContactForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContactsTable::configure($table);
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
            'index' => ListContacts::route('/'),
            'create' => CreateContact::route('/create'),
            'edit' => EditContact::route('/{record}/edit'),
        ];
    }
}
