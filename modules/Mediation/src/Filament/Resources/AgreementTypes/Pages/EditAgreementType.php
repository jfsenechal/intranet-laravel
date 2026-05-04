<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\AgreementTypes\Pages;

use AcMarche\Mediation\Filament\Resources\AgreementTypes\AgreementTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditAgreementType extends EditRecord
{
    #[Override]
    protected static string $resource = AgreementTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
