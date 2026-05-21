<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Recipients;

use AcMarche\Conseil\Filament\Resources\Recipients\Pages\CreateRecipient;
use AcMarche\Conseil\Filament\Resources\Recipients\Pages\EditRecipient;
use AcMarche\Conseil\Filament\Resources\Recipients\Pages\ListRecipients;
use AcMarche\Conseil\Filament\Resources\Recipients\Pages\ViewRecipient;
use AcMarche\Conseil\Filament\Resources\Recipients\RelationManagers\GroupsRelationManager;
use AcMarche\Conseil\Filament\Resources\Recipients\Schemas\RecipientForm;
use AcMarche\Conseil\Filament\Resources\Recipients\Tables\RecipientsTable;
use AcMarche\Conseil\Models\Recipient;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class RecipientResource extends Resource
{
    #[Override]
    protected static ?string $model = Recipient::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

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
            'last_name',
            'first_name',
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

    public static function getRelations(): array
    {
        return [
            GroupsRelationManager::class,
        ];
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
