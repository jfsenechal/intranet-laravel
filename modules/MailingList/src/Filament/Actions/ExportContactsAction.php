<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Actions;

use AcMarche\MailingList\Filament\Exports\ContactExport;
use AcMarche\MailingList\Models\Contact;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportContactsAction
{
    /**
     * @param  Closure(): Builder<Contact>  $query  Usually the table's getTableQueryForExport(), so search and sort apply.
     * @param  Closure(): string  $filename
     */
    public static function make(Closure $query, Closure $filename): Action
    {
        return Action::make('export')
            ->label('Exporter en XLSX')
            ->icon(Heroicon::ArrowDownTray)
            ->color('warning')
            ->schema([
                CheckboxList::make('columns')
                    ->label('Colonnes à exporter')
                    ->options(ContactExport::columns())
                    ->default(array_keys(ContactExport::columns()))
                    ->columns(2)
                    ->bulkToggleable()
                    ->required(),
            ])
            ->action(fn (array $data): StreamedResponse => new ContactExport($query(), $data['columns'])
                ->downloadXlsx($filename()));
    }
}
