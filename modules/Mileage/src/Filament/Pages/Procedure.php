<?php

namespace AcMarche\Mileage\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Procedure extends Page
{
    protected string $view = 'mileage::filament.pages.mileage-procedure';
    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Procédure';

    public static function getNavigationIcon(): ?string
    {
        return 'tabler-info-circle';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Procédure';
    }
}
