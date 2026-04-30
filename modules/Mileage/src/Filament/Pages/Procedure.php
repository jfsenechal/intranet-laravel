<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Pages;

use Filament\Pages\Page;
use Override;

final class Procedure extends Page
{
    #[Override]
    protected string $view = 'mileage::filament.pages.mileage-procedure';

    #[Override]
    protected static ?int $navigationSort = 4;

    #[Override]
    protected static ?string $navigationLabel = 'Procédure';

    public static function getNavigationIcon(): string
    {
        return 'tabler-info-circle';
    }

    public function getTitle(): string
    {
        return 'Procédure';
    }
}
