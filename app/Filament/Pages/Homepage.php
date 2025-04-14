<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;

class Homepage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.home';

    protected static ?string $navigationLabel = 'Accueil';
    protected static ?int $navigationSort = 1;

    public function getTitle(): string|Htmlable
    {
        return 'Accueil ';
    }

    public static function getNavigationLabel(): string
    {
        return 'Accueil';
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public function getLayout(): string
    {
        return static::$layout ?? 'filament-panels::components.layout.base';
    }

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Screen;
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }

}
