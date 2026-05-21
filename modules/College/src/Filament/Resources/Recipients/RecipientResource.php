<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Recipients;

use AcMarche\College\Filament\Resources\Recipients\Pages\CreateRecipient;
use AcMarche\College\Filament\Resources\Recipients\Pages\EditRecipient;
use AcMarche\College\Filament\Resources\Recipients\Pages\ListRecipients;
use AcMarche\College\Filament\Resources\Recipients\Pages\ViewRecipient;
use AcMarche\College\Filament\Resources\Recipients\Schemas\RecipientForm;
use AcMarche\College\Filament\Resources\Recipients\Tables\RecipientsTable;
use AcMarche\College\Models\Recipient;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class RecipientResource extends Resource
{
    #[Override]
    protected static ?string $model = Recipient::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $navigationLabel = 'Destinataires';

    #[Override]
    protected static ?string $modelLabel = 'destinataire';

    #[Override]
    protected static ?string $pluralModelLabel = 'destinataires';

    #[Override]
    protected static ?string $recordTitleAttribute = 'last_name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'nom',
            'prenom',
            'email',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return RecipientForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecipientsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecipients::route('/'),
            'create' => CreateRecipient::route('/create'),
            'view' => ViewRecipient::route('/{record}'),
            'edit' => EditRecipient::route('/{record}/edit'),
        ];
    }
}
