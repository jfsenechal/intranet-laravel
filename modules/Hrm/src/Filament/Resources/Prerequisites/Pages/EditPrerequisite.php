<?php

namespace AcMarche\Hrm\Filament\Resources\Prerequisites\Pages;

use AcMarche\Hrm\Filament\Resources\Prerequisites\PrerequisiteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditPrerequisite extends EditRecord
{
    protected static string $resource = PrerequisiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
