<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Rates\Pages;

use AcMarche\Mileage\Filament\Resources\Rates\RateResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditRate extends EditRecord
{
    #[Override]
    protected static string $resource = RateResource::class;

    public function getTitle(): string
    {
        return "Modification d'un tarif";
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
