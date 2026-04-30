<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\OperationalObjective;

use AcMarche\Pst\Filament\Resources\OperationalObjective\Pages\CreateOperationalObjective;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Pages\EditOperationalObjective;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Pages\ListOperationalObjectives;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Pages\ViewOperationalObjective;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Schemas\OperationalObjectiveForm;
use AcMarche\Pst\Filament\Resources\OperationalObjective\Tables\OperationalObjectiveTables;
use AcMarche\Pst\Models\OperationalObjective;
use BackedEnum;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Override;

final class OperationalObjectiveResource extends Resource
{
    #[Override]
    protected static ?string $model = OperationalObjective::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'tabler-target';

    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    #[Override]
    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'Objectif Opérationnel (OO)';
    }

    public static function table(Table $table): Table
    {
        return OperationalObjectiveTables::configure($table);
    }

    public static function form(Schema $schema): Schema
    {
        return OperationalObjectiveForm::configure($schema);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOperationalObjectives::route('/'),
            'create' => CreateOperationalObjective::route('/create'),
            'view' => ViewOperationalObjective::route('/{record}'),
            'edit' => EditOperationalObjective::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchResults(string $search): Collection
    {
        return self::getGlobalSearchEloquentQuery()
            ->whereKey(OperationalObjective::search($search)->keys())
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
