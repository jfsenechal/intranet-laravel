<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Forms\Components\RichEditor;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\HtmlString;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $this->configureTable();
        if (! app()->environment('production') && config('mail.redirect_to')) {
            Mail::alwaysTo(config('mail.redirect_to'));
        }
        $this->configureRichEditor();
        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
            fn (): View => view('filament.login_form'),
        );

        if (app()->environment('local')) {
            FilamentView::registerRenderHook(
                PanelsRenderHook::BODY_END,
                fn (): HtmlString => new HtmlString('<script src="http://localhost:8400/live.js"></script>'),
            );
        }
    }

    private function configureTable(): void
    {
        Table::configureUsing(function (Table $table): void {
            $table->striped()
                ->deferLoading();
        });
    }

    private function configureRichEditor(): void
    {
        RichEditor::configureUsing(function (RichEditor $richEditor): void {
            $richEditor->toolbarButtons([
                ['bold', 'italic', 'underline', 'strike', 'textColor', 'link', 'h2', 'h3'],
                ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
                ['bulletList', 'orderedList', 'blockquote', 'horizontalRule'],
                ['table', 'grid'],
                ['clearFormatting', 'undo', 'redo'],
            ]);
        });
    }
}
