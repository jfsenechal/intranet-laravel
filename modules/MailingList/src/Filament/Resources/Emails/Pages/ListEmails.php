<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Emails\Pages;

use AcMarche\MailingList\Filament\Resources\Emails\EmailResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\View\View;
use Override;

final class ListEmails extends ListRecords
{
    #[Override]
    protected static string $resource = EmailResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('rgpd')
                ->label('RGPD & Mailing Lists')
                ->icon(Heroicon::InformationCircle)
                ->color('info')
                ->modalHeading('Un rappel des règles RGPD concernant le Mailing Lists')
                ->modalContent(fn (): View => view('mailing-list-view::doc'))
                ->modalSubmitAction(false),
            CreateAction::make()
                ->label('Nouvel e-mail')
                ->icon(Heroicon::Plus),
        ];
    }
}
