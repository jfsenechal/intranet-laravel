<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Evaluations\Pages;

use AcMarche\Hrm\Filament\Resources\Evaluations\EvaluationResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditEvaluation extends EditRecord
{
    #[Override]
    protected static string $resource = EvaluationResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Evaluation '.$this->record->employee->last_name.' '.$this->record->employee->first_name;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }
}
