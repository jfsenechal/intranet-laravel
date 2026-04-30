<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst;

use AcMarche\Pst\Filament\Resources\ActionPst\Pages\CreateActionPst;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\EditActionPst;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\ListActionsAsGoogleSheet;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\ListActionsPst;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\ViewActionPst;
use AcMarche\Pst\Filament\Resources\ActionPst\RelationManagers\FollowUpsRelationManager;
use AcMarche\Pst\Filament\Resources\ActionPst\RelationManagers\HistoriesRelationManager;
use AcMarche\Pst\Filament\Resources\ActionPst\RelationManagers\MediasRelationManager;
use AcMarche\Pst\Filament\Resources\ActionPst\Schemas\ActionForm;
use AcMarche\Pst\Filament\Resources\ActionPst\Tables\ActionTables;
use AcMarche\Pst\Models\Action;
use BackedEnum;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\Resources\RelationManagers\RelationGroup;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Override;

// https://www.youtube.com/watch?v=85uRvsUvwJQ&list=PLqDySLfPKRn6fgrrdg4_SmsSxWzVlUQJo&index=23
// https://filamentphp.com/content/leandrocfe-navigating-filament-pages-with-previous-and-next-buttons
final class ActionPstResource extends Resource
{
    #[Override]
    protected static ?string $model = Action::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'tabler-bolt';

    // required for global search
    #[Override]
    protected static ?string $recordTitleAttribute = 'name';

    // Scout handles term splitting
    #[Override]
    protected static ?bool $shouldSplitGlobalSearchTerms = false;

    #[Override]
    protected static ?int $navigationSort = 4;

    #[Override]
    protected static ?string $navigationLabel = 'Liste des actions';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return ActionForm::configure($schema, null);
    }

    public static function table(Table $table): Table
    {
        return ActionTables::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationGroup::make('group', [
                MediasRelationManager::class,
                FollowUpsRelationManager::class,
                HistoriesRelationManager::class,
            ]),

        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActionsPst::route('/'),
            'create' => CreateActionPst::route('/create'),
            'view' => ViewActionPst::route('/{record}'),
            'edit' => EditActionPst::route('/{record}/edit'),
            'asGoogleSheet' => ListActionsAsGoogleSheet::route('/as/google/sheet'),
        ];
    }

    /**
     * Shows department and objective in results
     *
     * @return array<string, string|null>
     */
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Département' => $record->department,
            'Objectif opérationnel' => $record->operationalObjective?->name,
        ];
    }

    // Eager loads the operationalObjective relationship
    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['operationalObjective']);
    }

    public static function getGlobalSearchResults(string $search): Collection
    {
        return self::getGlobalSearchEloquentQuery()
            ->whereKey(Action::search($search)->keys())
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
