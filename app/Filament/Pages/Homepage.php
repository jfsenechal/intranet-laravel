<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;

class Homepage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.pages.home';

    public function getTitle(): string|Htmlable
    {
        return ' ';
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

}
