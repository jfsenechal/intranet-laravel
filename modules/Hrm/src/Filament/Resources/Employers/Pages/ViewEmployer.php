<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employers\Pages;

use AcMarche\Hrm\Filament\Resources\Employers\EmployerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewEmployer extends ViewRecord
{
    #[Override]
    protected static string $resource = EmployerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon(Heroicon::Pencil),
        ];
    }
}
