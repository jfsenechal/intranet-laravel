<?php

namespace AcMarche\Security\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

final class SecurityPage extends Page
{
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'security::filament.pages.security';

    public static function getNavigationLabel(): string
    {
        return 'Security page';
    }

    public static function canAccess(): bool
    {
        return true;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Page title security';
    }
}
