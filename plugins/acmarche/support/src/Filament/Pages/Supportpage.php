<?php

namespace AcMarche\Support\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;

class Supportpage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'acsupport::filament.pages.support';
    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return 'Support page';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Page title support';
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
