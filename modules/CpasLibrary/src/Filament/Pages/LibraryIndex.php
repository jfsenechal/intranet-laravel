<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Categories\CategorieResource;
use AcMarche\CpasLibrary\Models\Category;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
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
}
