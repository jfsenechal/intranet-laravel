<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Pages;

use AcMarche\Courrier\Filament\Pages\IncomingMailSearch;
use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Override;

final class ListIncomingMails extends ListRecords
{
    #[Override]
    protected static string $resource = IncomingMailResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        $searchUrl = IncomingMailSearch::getUrl();

        return new HtmlString(
            'Cette liste n\'affiche que les <strong>courriers du jour</strong>. Pour rechercher dans l\'ensemble des courriers, '
            .'<a href="'.e($searchUrl).'" class="font-semibold underline hover:no-underline">utilisez la recherche avancée</a>.'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un courrier')
                ->icon('tabler-plus'),
        ];
    }
}
