<?php

namespace AcMarche\News\Filament\Resources\NewsResource\Pages;

use AcMarche\News\Filament\Resources\NewsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Spatie\LaravelPdf\Facades\Pdf;

class ListNews extends ListRecords
{
    protected static string $resource = NewsResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' actualités';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une actualité')
                ->icon('tabler-plus'),
        ];
    }

    public function mount(): void
    {
        Pdf::html(view('pdf.test', [
            'invoiceNumber' => '1234',
            'customerName' => 'Grumpy Cat',

        ]))
            ->save('invoice.pdf');
    }

}
