<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Members;

use AcMarche\SportsActivities\Filament\Resources\Members\Pages\CreateMember;
use AcMarche\SportsActivities\Filament\Resources\Members\Pages\EditMember;
use AcMarche\SportsActivities\Filament\Resources\Members\Pages\ListMembers;
use AcMarche\SportsActivities\Filament\Resources\Members\Pages\ViewMember;
use AcMarche\SportsActivities\Filament\Resources\Members\Schemas\MemberForm;
use AcMarche\SportsActivities\Filament\Resources\Members\Tables\MembersTable;
use AcMarche\SportsActivities\Models\Member;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Override;

final class MemberResource extends Resource
{
    #[Override]
    protected static ?string $model = Member::class;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUser;

    #[Override]
    protected static ?int $navigationSort = 3;

    #[Override]
    protected static ?string $navigationLabel = 'Sportifs';

    #[Override]
    protected static ?string $modelLabel = 'sportif';

    #[Override]
    protected static ?string $pluralModelLabel = 'sportifs';

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

    public static function table(Table $table): Table
    {
        return MembersTable::configure($table);
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
            'index' => ListMembers::route('/'),
            'create' => CreateMember::route('/create'),
            'view' => ViewMember::route('/{record}'),
            'edit' => EditMember::route('/{record}/edit'),
        ];
    }
}
