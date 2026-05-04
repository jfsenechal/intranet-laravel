<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\AgreementTypes\Pages;

use AcMarche\Mediation\Filament\Resources\AgreementTypes\AgreementTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

final class ViewAgreementType extends ViewRecord
{
    #[Override]
    protected static string $resource = AgreementTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()->icon('tabler-edit'),
            DeleteAction::make()->icon('tabler-trash'),
        ];
    }
}
