<?php

namespace AcMarche\Hrm\Filament\Resources\Services\Pages;

use AcMarche\Hrm\Filament\Resources\Services\ServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

final class ViewService extends ViewRecord
{
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
