<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Contacts;

use AcMarche\MailingList\Filament\Resources\Contacts\Pages\CreateContact;
use AcMarche\MailingList\Filament\Resources\Contacts\Pages\EditContact;
use AcMarche\MailingList\Filament\Resources\Contacts\Pages\ListContacts;
use AcMarche\MailingList\Filament\Resources\Contacts\Schemas\ContactForm;
use AcMarche\MailingList\Filament\Resources\Contacts\Tables\ContactsTable;
use AcMarche\MailingList\Models\Contact;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class ContactResource extends Resource
{
    #[Override]
    protected static ?string $model = Contact::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $navigationLabel = 'Contacts';

    #[Override]
    protected static ?string $modelLabel = 'contact';

    #[Override]
    protected static ?string $pluralModelLabel = 'contacts';

    #[Override]
    protected static ?string $recordTitleAttribute = 'email';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'first_name',
            'last_name',
            'email',
        ];
    }

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
