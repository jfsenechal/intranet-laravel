<?php

namespace AcMarche\App\Traits;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;

trait HooksTrait
{
    public function buttonListModules(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_NAV_START,
            fn (): View => view('app::filament.list-modules-button'),
        );
    }
}
