<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\SmsReminders\Pages;

use AcMarche\Hrm\Filament\Resources\SmsReminders\SmsReminderResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditSmsReminder extends EditRecord
{
    #[Override]
    protected static string $resource = SmsReminderResource::class;

    public function getTitle(): string|Htmlable
    {
        $employee = $this->record->employee;

        if ($employee === null) {
            return 'Rappel SMS';
        }

        return 'Rappel SMS de '.$employee->last_name.' '.$employee->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }
}
