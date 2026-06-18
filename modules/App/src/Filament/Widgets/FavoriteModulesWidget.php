<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Widgets;

use AcMarche\App\Handler\FavoriteModuleHandler;
use AcMarche\Security\Models\Module;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Override;

final class FavoriteModulesWidget extends Widget
{
    #[Override]
    protected string $view = 'app::filament.widgets.favorite-modules';

    #[Override]
    protected int|string|array $columnSpan = 'full';

    #[Override]
    protected static ?int $sort = -5;

    /**
     * @return Collection<int, Module>
     */
    public function getFavorites(): Collection
    {
        return FavoriteModuleHandler::getFavoriteModules();
    }
}
