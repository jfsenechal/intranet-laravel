<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Filament\Pages;

use AcMarche\WhoIsWho\Filament\Concerns\InteractsWithFavoriteEmployees;
use AcMarche\WhoIsWho\Repository\FavoriteEmployeeRepository;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Override;

final class Favorites extends Page
{
    use InteractsWithFavoriteEmployees;

    #[Override]
    protected string $view = 'who-is-who::filament.pages.favorites';

    #[Override]
    protected static ?int $navigationSort = 0;

    /**
     * The rest of the panel is open to guests, but a personal favorites list
     * needs a user: the navigation entry is hidden and the route returns 403.
     */
    #[Override]
    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public static function getNavigationLabel(): string
    {
        return 'Mes favoris';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-star';
    }

    public function getTitle(): string
    {
        return 'Mes complices du quotidien';
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getViewData(): array
    {
        return [
            'employees' => FavoriteEmployeeRepository::favorites(),
        ];
    }
}
