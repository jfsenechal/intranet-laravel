<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employers\Pages;

use AcMarche\Hrm\Filament\Resources\Employers\EmployerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListEmployers extends ListRecords
{
    #[Override]
    protected static string $resource = EmployerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un employeur')
                ->icon('tabler-plus'),
        ];
    }
}
