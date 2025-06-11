<?php

namespace AcMarche\App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Supportpage extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-document-text';
    protected string $view = 'acapp::filament.pages.support';
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
}
