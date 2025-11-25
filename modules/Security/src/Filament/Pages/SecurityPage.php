<?php

namespace AcMarche\Security\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class SecurityPage extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-document-text';
    protected string $view = 'acsecurity::filament.pages.security';

    public static function getNavigationLabel(): string
    {
        return 'Security page';
    }

    public function getTitle(): string|Htmlable
    {
        return 'Page title security';
    }

    public static function canAccess(): bool
    {
        return true;
    }

}
