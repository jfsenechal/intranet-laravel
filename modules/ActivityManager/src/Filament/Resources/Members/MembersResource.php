<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Members;

use AcMarche\ActivityManager\Filament\Resources\Members\Pages\CreateMember;
use AcMarche\ActivityManager\Filament\Resources\Members\Pages\EditMember;
use AcMarche\ActivityManager\Filament\Resources\Members\Pages\ListMembers;
use AcMarche\ActivityManager\Filament\Resources\Members\Pages\ViewMember;
use AcMarche\ActivityManager\Filament\Resources\Members\RelationManagers\ActivitiesRelationManager;
use AcMarche\ActivityManager\Filament\Resources\Members\Schemas\MemberForm;
use AcMarche\ActivityManager\Filament\Resources\Members\Schemas\MemberInfolist;
use AcMarche\ActivityManager\Filament\Resources\Members\Tables\MembersTable;
use AcMarche\ActivityManager\Models\Member;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;
use UnitEnum;

final class MembersResource extends Resource
{
    #[Override]
    protected static ?string $model = Member::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Activités';

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $navigationLabel = 'Members';

    #[Override]
    protected static ?string $modelLabel = 'membre';

    #[Override]
    protected static ?string $pluralModelLabel = 'membres';

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
        return MemberForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MemberInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ActivitiesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembers::route('/'),
            'create' => CreateMember::route('/create'),
            'view' => ViewMember::route('/{record}'),
            'edit' => EditMember::route('/{record}/edit'),
        ];
    }
}
