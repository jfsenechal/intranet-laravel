<?php

namespace AcMarche\Security\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Contracts\Support\Htmlable;

class SecurityPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'ac-security::filament.pages.security';

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

    public function getMaxContentWidth22(): MaxWidth
    {
        return MaxWidth::Screen;
    }

}
