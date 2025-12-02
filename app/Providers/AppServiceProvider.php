<?php

namespace App\Providers;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::shouldBeStrict();
        Model::automaticallyEagerLoadRelationships();

        if (! app()->environment('production')) {
            Mail::alwaysTo(config('mail.webmaster_email'));
        }

        $this->configureTable();
        $this->configureRichEditor();
        $this->configureForm();
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
                ['table', 'grid'],
                ['clearFormatting', 'undo', 'redo'],
            ]);
        });
    }

    private function configureForm(): void
    {
        return;
        TextInput::configureUsing(function (TextInput $config) {
            $config->inlineLabel();
        });
        Select::configureUsing(function (Select $config) {
            $config->inlineLabel();
        });
        DatePicker::configureUsing(function (DatePicker $config) {
            $config->inlineLabel();
        });
        Section::configureUsing(function (Section $config) {
            $config
                ->columns()
                ->compact();
        });

    }
}
