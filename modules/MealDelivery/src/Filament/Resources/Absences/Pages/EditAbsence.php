<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Absences\Pages;

use AcMarche\MealDelivery\Filament\Resources\Absences\AbsenceResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditAbsence extends EditRecord
{
    #[Override]
    protected static string $resource = AbsenceResource::class;

    public function getTitle(): string
    {
        return 'Edit absence';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
