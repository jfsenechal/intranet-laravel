<?php

namespace AcMarche\Hrm\Filament\Resources\PayScales\Pages;

use AcMarche\Hrm\Filament\Resources\PayScales\PayScaleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

final class ListPayScales extends ListRecords
{
    protected static string $resource = PayScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Ajouter une échelle')
                ->icon('tabler-plus'),
        ];
    }
}
