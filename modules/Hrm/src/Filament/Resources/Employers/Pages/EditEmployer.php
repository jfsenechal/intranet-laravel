<?php

namespace AcMarche\Hrm\Filament\Resources\Employers\Pages;

use AcMarche\Hrm\Filament\Resources\Employers\EmployerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditEmployer extends EditRecord
{
    protected static string $resource = EmployerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
