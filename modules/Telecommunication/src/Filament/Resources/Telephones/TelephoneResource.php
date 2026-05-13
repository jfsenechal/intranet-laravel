<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\Telephones;

use AcMarche\Telecommunication\Filament\Resources\Telephones\Pages\CreateTelephone;
use AcMarche\Telecommunication\Filament\Resources\Telephones\Pages\EditTelephone;
use AcMarche\Telecommunication\Filament\Resources\Telephones\Pages\ListTelephones;
use AcMarche\Telecommunication\Filament\Resources\Telephones\Pages\ViewTelephone;
use AcMarche\Telecommunication\Filament\Resources\Telephones\RelationManagers\AttachmentsRelationManager;
use AcMarche\Telecommunication\Filament\Resources\Telephones\Schemas\TelephoneForm;
use AcMarche\Telecommunication\Filament\Resources\Telephones\Tables\TelephonesTable;
use AcMarche\Telecommunication\Models\Telephone;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class TelephoneResource extends Resource
{
    #[Override]
    protected static ?string $model = Telephone::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $navigationLabel = 'Téléphones';

    #[Override]
    protected static ?string $modelLabel = 'téléphone';

    #[Override]
    protected static ?string $pluralModelLabel = 'téléphones';

    #[Override]
    protected static ?string $recordTitleAttribute = 'user_name';

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'user_name',
            'number',
            'service',
            'department',
            'location',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return TelephoneForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TelephonesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTelephones::route('/'),
            'create' => CreateTelephone::route('/create'),
            'view' => ViewTelephone::route('/{record}'),
            'edit' => EditTelephone::route('/{record}/edit'),
        ];
    }
}
