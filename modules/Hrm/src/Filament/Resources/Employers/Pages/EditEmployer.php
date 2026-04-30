<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employers\Pages;

use AcMarche\Hrm\Filament\Resources\Employers\EmployerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditEmployer extends EditRecord
{
    #[Override]
    protected static string $resource = EmployerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
