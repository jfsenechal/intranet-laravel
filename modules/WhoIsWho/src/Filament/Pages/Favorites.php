<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Filament\Pages;

use AcMarche\WhoIsWho\Filament\Concerns\InteractsWithFavoriteEmployees;
use AcMarche\WhoIsWho\Repository\FavoriteEmployeeRepository;
use Filament\Pages\Page;
use Override;

final class Favorites extends Page
{
    use InteractsWithFavoriteEmployees;

    #[Override]
    protected string $view = 'who-is-who::filament.pages.favorites';

    #[Override]
    protected static ?int $navigationSort = 0;

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
        return 'Mes agents favoris';
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
