<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\HealthInsurances\Pages;

use AcMarche\Hrm\Filament\Resources\HealthInsurances\HealthInsuranceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListHealthInsurances extends ListRecords
{
    #[Override]
    protected static string $resource = HealthInsuranceResource::class;

    protected static ?string $title = 'Mutuelles';

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une mutuelle')
                ->icon('tabler-plus'),
        ];
    }
}
