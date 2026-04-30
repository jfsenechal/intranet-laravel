<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Users;

use AcMarche\Pst\Enums\NavigationGroupEnum;
use AcMarche\Pst\Filament\Resources\Users\Pages\EditUser;
use AcMarche\Pst\Filament\Resources\Users\Pages\ListUsers;
use AcMarche\Pst\Filament\Resources\Users\Pages\ViewUser;
use AcMarche\Pst\Filament\Resources\Users\Schemas\UserForm;
use AcMarche\Pst\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Override;
use UnitEnum;

final class UserResource extends Resource
{
    #[Override]
    protected static ?string $model = User::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-users';

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::Settings;

    #[Override]
    protected static ?string $recordTitleAttribute = 'fullName';

    public static function getModelLabel(): string
    {
        return 'Agents';
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResults(string $search): Collection
    {
        return self::getGlobalSearchEloquentQuery()
            ->whereKey(User::search($search)->keys())
            ->limit(self::$globalSearchResultsLimit)
            ->get()
            ->map(function (Model $record): ?GlobalSearchResult {
                $url = self::getGlobalSearchResultUrl($record);

                if (blank($url)) {
                    return null;
                }

                return new GlobalSearchResult(
                    title: self::getGlobalSearchResultTitle($record),
                    url: $url,
                    details: self::getGlobalSearchResultDetails($record),
                    actions: self::getGlobalSearchResultActions($record),
                );
            })
            ->filter();
    }
}
