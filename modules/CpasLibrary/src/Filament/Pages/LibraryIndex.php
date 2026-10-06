<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Pages;

use AcMarche\CpasLibrary\Enums\FicheTypeEnum;
use AcMarche\CpasLibrary\Filament\Resources\Categories\CategorieResource;
use AcMarche\CpasLibrary\Filament\Resources\Fiches\FicheResource;
use AcMarche\CpasLibrary\Models\Category;
use AcMarche\CpasLibrary\Models\Fiche;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Override;
use UnitEnum;

final class LibraryIndex extends Page
{
    #[Override]
    protected string $view = 'cpas-library::filament.pages.library-index';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Bibliothèque';

    #[Override]
    protected static ?int $navigationSort = 0;

    #[Override]
    protected static ?string $navigationLabel = 'Bibliothèque de l\'information';

    #[Override]
    protected static ?string $title = 'Bibliothèque de l\'information';

    protected static ?string $slug = 'bibliotheque';

    public static function canAccess(): bool
    {
        return Gate::forUser(Filament::auth()->user())->allows('viewAny', Category::class);
    }

    /**
     * Parent categories with their direct children and fiche counts.
     *
     * @return Collection<int, Category>
     */
    public function getParentCategories(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->with([
                'children' => fn ($query) => $query
                    ->withCount('fiches')
                    ->orderBy('name'),
            ])
            ->orderBy('name')
            ->get();
    }

    public function getCategoryUrl(Category $category): string
    {
        return CategorieResource::getUrl('view', ['record' => $category]);
    }

    /**
     * The ten most recently created fiches, absences excepted. Legacy rows may
     * have no type, so a null type counts as "not an absence".
     *
     * @return Collection<int, Fiche>
     */
    public function getLatestFiches(): Collection
    {
        return Fiche::query()
            ->where(fn (Builder $query) => $query
                ->whereNull('type')
                ->orWhere('type', '!=', FicheTypeEnum::ABSENCE->value))
            ->with('category')
            ->latest('createdAt')
            ->limit(10)
            ->get();
    }

    /**
     * Absences that have not ended yet, soonest first. Past absences are
     * purged daily by `cpas-library:remove-expired`, the filter just keeps
     * them out until that runs.
     *
     * @return Collection<int, Fiche>
     */
    public function getAbsences(): Collection
    {
        return Fiche::query()
            ->where('type', FicheTypeEnum::ABSENCE->value)
            ->where(fn (Builder $query) => $query
                ->whereNull('date_end')
                ->orWhereDate('date_end', '>=', Carbon::today()))
            ->orderBy('date_begin')
            ->get();
    }

    public function getFicheUrl(Fiche $fiche): string
    {
        return FicheResource::getUrl('view', ['record' => $fiche]);
    }

    protected function getHeaderActions(): array
    {
        return [
            FicheResource::createActionGroup(),
        ];
    }
}
