<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Pages;

use AcMarche\Courrier\Filament\Actions\AskAction;
use AcMarche\Courrier\Filament\Actions\DownloadAction;
use AcMarche\Courrier\Filament\Actions\ShareAction;
use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Schemas\IncomingMailInfolist;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Override;

final class ViewIncomingMail extends ViewRecord
{
    #[Override]
    protected static string $resource = IncomingMailResource::class;

    public function getTitle(): string
    {
        return 'Courrier du '.$this->record->mail_date?->translatedFormat('d F Y').' de '.$this->record->sender;
    }

    public function infolist(Schema $schema): Schema
    {
        return IncomingMailInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            DownloadAction::make(),
            ShareAction::make(),
            AskAction::make(),
            Action::make('back')
                ->label('Retour à la liste')
                ->icon('tabler-list')
                ->url(IncomingMailResource::getUrl('index')),
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }


}
