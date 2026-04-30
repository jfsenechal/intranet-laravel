<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\SmsReminders\Pages;

use AcMarche\Hrm\Filament\Resources\SmsReminders\SmsReminderResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;
use Override;

final class ListSmsReminders extends ListRecords
{
    #[Override]
    protected static string $resource = SmsReminderResource::class;

    public function getTitle(): string|Htmlable
    {
        return $this->getAllTableRecordsCount().' rappels SMS';
    }

    public function getSubheading(): Htmlable
    {
        return new HtmlString(
            'L\'historique complet des sms est consultable sur le site <a href="https://icom.myinforius.be" target="_blank" class="text-primary-600 hover:underline">https://icom.myinforius.be</a>'
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sendSms')
                ->label('Envoyer un SMS')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('gray')
                ->url(SmsReminderResource::getUrl('send')),
            Action::make('history')
                ->label('Historique API')
                ->icon(Heroicon::Clock)
                ->color('gray')
                ->url(SmsReminderResource::getUrl('history')),
            CreateAction::make(),
        ];
    }
}
