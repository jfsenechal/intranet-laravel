<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\AgreementTypes\Pages;

use AcMarche\Mediation\Filament\Resources\AgreementTypes\AgreementTypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListAgreementTypes extends ListRecords
{
    #[Override]
    protected static string $resource = AgreementTypeResource::class;

    public function getTitle(): string
    {
        return "Types d'accord";
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau type')
                ->icon('tabler-plus'),
        ];
    }
}
