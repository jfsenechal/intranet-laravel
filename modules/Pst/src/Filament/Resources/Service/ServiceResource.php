<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Service;

use AcMarche\Pst\Enums\NavigationGroupEnum;
use AcMarche\Pst\Filament\Resources\Service\Pages\CreateService;
use AcMarche\Pst\Filament\Resources\Service\Pages\EditService;
use AcMarche\Pst\Filament\Resources\Service\Pages\ListServices;
use AcMarche\Pst\Filament\Resources\Service\Pages\ViewService;
use AcMarche\Pst\Filament\Resources\Service\Schemas\ServiceForm;
use AcMarche\Pst\Filament\Resources\Service\Tables\ServiceTables;
use AcMarche\Pst\Models\Service;
use BackedEnum;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Override;
use UnitEnum;

final class ServiceResource extends Resource
{
    #[Override]
    protected static ?string $model = Service::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'tabler-users-group';

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = NavigationGroupEnum::Settings;

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    protected static ?bool $shouldSplitGlobalSearchTerms = false;

    public static function form(Schema $schema): Schema
    {
        return ServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServices::route('/'),
            'create' => CreateService::route('/create'),
            'view' => ViewService::route('/{record}'),
            'edit' => EditService::route('/{record}/edit'),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Initiales' => $record->initials,
        ];
    }

    public static function getGlobalSearchResults(string $search): Collection
    {
        return self::getGlobalSearchEloquentQuery()
            ->whereKey(Service::search($search)->keys())
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
