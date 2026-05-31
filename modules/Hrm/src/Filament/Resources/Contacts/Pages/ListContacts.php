<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Contacts\Pages;

use AcMarche\Hrm\Filament\Exports\ContactExport;
use AcMarche\Hrm\Filament\Resources\Contacts\ContactResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListContacts extends ListRecords
{
    #[Override]
    protected static string $resource = ContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau contact')
                ->icon(Heroicon::Plus),
            Action::make('export')
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
                ->action(fn (array $data) => new ContactExport($this->getFilteredTableQuery(), $data['columns'])->downloadXlsx('contacts.xlsx')),
        ];
    }
}
