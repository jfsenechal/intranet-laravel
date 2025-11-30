<?php

namespace App\Filament\Admin\Pages;

use AcMarche\Security\Models\Tab;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

final class Homepage extends Page
{
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.pages.home';

    protected static ?string $navigationLabel = 'Accueil';

    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string
    {
        return 'Accueil';
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Accueil ';
    }

    public function getLayout(): string
    {
        return self::$layout ?? 'filament-panels::components.layout.base';
    }

    public function getMaxContentWidth(): Width|null|string
    {
        return Width::Screen;
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }

    /**
     * Get all tabs with their modules
     */
    public function getTabsWithModules(): Collection
    {
        return Tab::with(['modules' => function ($query) {
            $query->orderBy('name');
        }])
            ->orderBy('name')
            ->get();
    }
}
