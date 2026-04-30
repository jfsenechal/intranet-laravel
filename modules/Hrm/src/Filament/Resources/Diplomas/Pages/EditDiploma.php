<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Diplomas\Pages;

use AcMarche\Hrm\Filament\Resources\Diplomas\DiplomaResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditDiploma extends EditRecord
{
    #[Override]
    protected static string $resource = DiplomaResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Modification diplôme de '.$this->record->employee->last_name.' '.$this->record->employee->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }
}
