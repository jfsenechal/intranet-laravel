<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\JobFunctions\Pages;

use AcMarche\Hrm\Filament\Resources\JobFunctions\JobFunctionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListJobFunctions extends ListRecords
{
    #[Override]
    protected static string $resource = JobFunctionResource::class;

    protected ?string $subheading = 'La liste des fonctions est utilisée pour les candidatures';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une fonction')
                ->icon('tabler-plus'),
        ];
    }
}
