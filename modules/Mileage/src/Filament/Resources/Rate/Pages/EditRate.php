<?php

namespace AcMarche\Mileage\Filament\Resources\Rate\Pages;

use AcMarche\Mileage\Filament\Resources\RateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditRate extends EditRecord
{
    protected static string $resource = RateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->icon('tabler-eye'),
        ];
    }

    public function getTitle(): string|Htmlable
    {
        return "Modification d'un tarif";
    }
}
