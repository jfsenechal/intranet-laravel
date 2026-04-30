<?php

declare(strict_types=1);

namespace App\Providers;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Infolists\Components\Entry;
use Filament\Support\Components\Component;
use Filament\Support\Concerns\Configurable;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\BaseFilter;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Mail;
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
        $this->translatableComponents();
        if (! app()->environment('production') && config('mail.redirect_to')) {
            Mail::alwaysTo(config('mail.redirect_to'));
        }
        $this->configureRichEditor();
        FilamentView::registerRenderHook(
            PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
            fn (): View => view('filament.login_form'),
        );
    }

    private function translatableComponents(): void
    {
        foreach ([Field::class, BaseFilter::class, Placeholder::class, Column::class, Entry::class] as $component) {
            /* @var Configurable $component */
            $component::configureUsing(function (Component $translatable): void {
                /** @phpstan-ignore method.notFound */
                $translatable->translateLabel();
            });
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
                ['bold', 'italic', 'strike', 'textColor', 'link', 'h2', 'h3'],
                ['alignStart', 'alignCenter', 'alignEnd', 'alignJustify'],
                ['bulletList', 'orderedList', 'blockquote', 'horizontalRule'],
                ['table', 'grid', 'attachFiles'],
                ['clearFormatting', 'undo', 'redo'],
            ]);
        });
    }
}
