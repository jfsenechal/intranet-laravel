<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class Homepage extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-document-text';
    protected  string $view = 'filament.pages.home';

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

    public function getMaxContentWidth(): Width|null|string
    {
        return Width::Screen;
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }

}
