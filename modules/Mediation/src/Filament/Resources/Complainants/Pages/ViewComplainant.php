<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\Complainants\Pages;

use AcMarche\Mediation\Filament\Resources\Complainants\ComplainantResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

final class ViewComplainant extends ViewRecord
{
    #[Override]
    protected static string $resource = ComplainantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->icon('tabler-edit'),
            DeleteAction::make()->icon('tabler-trash'),
        ];
    }
}
