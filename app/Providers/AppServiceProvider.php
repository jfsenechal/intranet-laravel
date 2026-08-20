<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\RichEditor;
use Filament\Support\Facades\FilamentTimezone;
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
        FilamentTimezone::set(config('app.display_timezone'));
        $this->configureTable();
        if (! app()->environment('production') && config('mail.redirect_to')) {
            Mail::alwaysTo(config('mail.redirect_to'));
        }
        $this->configureRichEditor();
        $this->configureDeleteBulkAction();
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

    /**
     * Authorize bulk deletion one record at a time.
     *
     * Filament checks `deleteAny()` for a DeleteBulkAction, and when a policy
     * omits that method the check falls through to `Response::allow()` — the
     * bulk delete is then open to anyone who can reach the list, even though
     * the row action next to it is refused. Only a handful of the policies in
     * this application define `deleteAny()`.
     *
     * Passing no argument hands the check to Filament's own resolver, which
     * runs `delete()` through the resource authorization. That keeps the
     * semantics identical to the row action, including for the models that
     * have no policy at all, where deletion stays allowed as it is today.
     */
    private function configureDeleteBulkAction(): void
    {
        DeleteBulkAction::configureUsing(function (DeleteBulkAction $action): void {
            $action->authorizeIndividualRecords();
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
