<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Groups;

use AcMarche\Conseil\Filament\Resources\Groups\Pages\CreateGroup;
use AcMarche\Conseil\Filament\Resources\Groups\Pages\EditGroup;
use AcMarche\Conseil\Filament\Resources\Groups\Pages\ListGroups;
use AcMarche\Conseil\Filament\Resources\Groups\Pages\ViewGroup;
use AcMarche\Conseil\Filament\Resources\Groups\RelationManagers\AttachmentsRelationManager;
use AcMarche\Conseil\Filament\Resources\Groups\RelationManagers\RecipientsRelationManager;
use AcMarche\Conseil\Filament\Resources\Groups\Schemas\GroupForm;
use AcMarche\Conseil\Filament\Resources\Groups\Tables\GroupsTable;
use AcMarche\Conseil\Models\Group;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class GroupResource extends Resource
{
    #[Override]
    protected static ?string $model = Group::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Groupes';

    #[Override]
    protected static ?string $modelLabel = 'groupe';

    #[Override]
    protected static ?string $pluralModelLabel = 'groupes';

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
        return GroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RecipientsRelationManager::class,
            AttachmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGroups::route('/'),
            'create' => CreateGroup::route('/create'),
            'view' => ViewGroup::route('/{record}'),
            'edit' => EditGroup::route('/{record}/edit'),
        ];
    }
}
