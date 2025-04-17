<?php

namespace AcMarche\App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;

class Supportpage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'acapp::filament.pages.support';
    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return 'App page';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Page title App';
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public function getMaxContentWidth22(): MaxWidth
    {
        return MaxWidth::Screen;
    }

}
