<?php

namespace AcMarche\Mileage\Filament\Resources\Rates\Pages;

use AcMarche\Mileage\Filament\Resources\Rates\RateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

final class EditRate extends EditRecord
{
    protected static string $resource = RateResource::class;

    public function getTitle(): string|Htmlable
    {
        return "Modification d'un tarif";
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }
}
