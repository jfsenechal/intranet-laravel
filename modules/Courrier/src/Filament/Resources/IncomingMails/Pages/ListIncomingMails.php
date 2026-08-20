<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Pages;

use AcMarche\Courrier\Filament\Pages\DraftIncomingMails;
use AcMarche\Courrier\Filament\Pages\IncomingMailSearch;
use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Override;

final class ListIncomingMails extends ListRecords
{
    #[Override]
    protected static string $resource = IncomingMailResource::class;

    protected static ?string $title = 'Courriers du jour';

    public function getSubheading(): string|Htmlable|null
    {
        return new HtmlString(
            view('courrier::filament.incoming-mails.subheading', [
                'searchUrl' => IncomingMailSearch::getUrl(),
            ])->render()
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            $this->reviewDraftsAction(),
            CreateAction::make()
                ->label('Ajouter un courrier')
                ->icon('tabler-plus'),
        ];
    }

    /**
     * Shortcut to the drafts page. They are excluded from this listing until
     * they are validated, so it is worth saying here that some are waiting.
     */
    private function reviewDraftsAction(): Action
    {
        $draftCount = IncomingMailRepository::drafts()->count();

        return Action::make('reviewDrafts')
            ->label(sprintf('Brouillons IA (%d)', $draftCount))
            ->icon('tabler-sparkles')
            ->color(Color::Indigo)
            ->visible($draftCount > 0 && DraftIncomingMails::canAccess())
            ->url(DraftIncomingMails::getUrl());
    }
}
