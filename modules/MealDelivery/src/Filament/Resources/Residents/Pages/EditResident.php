<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Residents\Pages;

use AcMarche\MealDelivery\Filament\Resources\Residents\ResidentResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditResident extends EditRecord
{
    #[Override]
    protected static string $resource = ResidentResource::class;

    public function getTitle(): string
    {
        return $this->record->fullName();
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
