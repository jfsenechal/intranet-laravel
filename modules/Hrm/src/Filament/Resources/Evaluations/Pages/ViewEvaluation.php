<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Evaluations\Pages;

use AcMarche\Hrm\Filament\Actions\BackToEmployeeAction;
use AcMarche\Hrm\Filament\Resources\Employees\EmployeeResource;
use AcMarche\Hrm\Filament\Resources\Evaluations\EvaluationResource;
use AcMarche\Hrm\Models\Evaluation;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class ViewEvaluation extends ViewRecord
{
    #[Override]
    protected static string $resource = EvaluationResource::class;

    public function getTitle(): string|Htmlable
    {
        /** @var Evaluation $evaluation */
        $evaluation = $this->record;

        return $evaluation->employee !== null
            ? 'Évaluation de '.$evaluation->employee->full_name
            : 'Évaluation';
    }

    protected function getHeaderActions(): array
    {
        /** @var Evaluation $evaluation */
        $evaluation = $this->record;

        return [
            BackToEmployeeAction::make(),
            EditAction::make()
                ->icon(Heroicon::Pencil),
            DeleteAction::make()
                ->icon(Heroicon::Trash)
                ->successRedirectUrl(fn (): string => EmployeeResource::getUrl('view', ['record' => $evaluation->employee_id])),
        ];
    }
}
