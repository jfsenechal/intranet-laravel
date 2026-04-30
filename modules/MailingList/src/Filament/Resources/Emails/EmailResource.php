<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Emails;

use AcMarche\MailingList\Filament\Resources\Emails\Pages\CreateEmail;
use AcMarche\MailingList\Filament\Resources\Emails\Pages\EditEmail;
use AcMarche\MailingList\Filament\Resources\Emails\Pages\ListEmails;
use AcMarche\MailingList\Filament\Resources\Emails\Pages\ViewEmail;
use AcMarche\MailingList\Filament\Resources\Emails\Schemas\EmailForm;
use AcMarche\MailingList\Filament\Resources\Emails\Tables\EmailsTable;
use AcMarche\MailingList\Models\Email;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class EmailResource extends Resource
{
    #[Override]
    protected static ?string $model = Email::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $navigationLabel = 'E-mails';

    #[Override]
    protected static ?string $modelLabel = 'e-mail';

    #[Override]
    protected static ?string $pluralModelLabel = 'e-mails';

    #[Override]
    protected static ?string $recordTitleAttribute = 'subject';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'subject',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return EmailForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmails::route('/'),
            'create' => CreateEmail::route('/create'),
            'view' => ViewEmail::route('/{record}'),
            'edit' => EditEmail::route('/{record}/edit'),
        ];
    }
}
