<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\SmsReminders\Pages;

use AcMarche\Hrm\Filament\Actions\BackToEmployeeAction;
use AcMarche\Hrm\Filament\Resources\SmsReminders\SmsReminderResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ViewSmsReminder extends ViewRecord
{
    #[Override]
    protected static string $resource = SmsReminderResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Rappel SMS de '.$this->record->employee->last_name.' '.$this->record->employee->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            BackToEmployeeAction::make(),
            Action::make('send')
                ->label('Envoyer le SMS')
                ->icon(Heroicon::PaperAirplane)
                ->color('primary')
                ->url(fn (): string => SmsReminderResource::getUrl('send', ['record' => $this->record])),
            EditAction::make()
                ->icon(Heroicon::Pencil),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
