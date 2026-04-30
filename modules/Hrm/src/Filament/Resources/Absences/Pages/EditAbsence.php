<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Absences\Pages;

use AcMarche\Hrm\Filament\Resources\Absences\AbsenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

final class EditAbsence extends EditRecord
{
    protected static string $resource = AbsenceResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Modification absence de '.$this->record->employee->last_name.' '.$this->record->employee->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon(Heroicon::Eye),
            Actions\DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
