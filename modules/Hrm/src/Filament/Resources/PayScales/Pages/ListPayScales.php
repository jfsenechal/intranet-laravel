<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\PayScales\Pages;

use AcMarche\Hrm\Filament\Resources\PayScales\PayScaleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListPayScales extends ListRecords
{
    #[Override]
    protected static string $resource = PayScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter une échelle')
                ->icon('tabler-plus'),
        ];
    }
}
