<?php

namespace AcMarche\Hrm\Filament\Resources\PayScales\Pages;

use AcMarche\Hrm\Filament\Resources\PayScales\PayScaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

final class EditPayScale extends EditRecord
{
    protected static string $resource = PayScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
