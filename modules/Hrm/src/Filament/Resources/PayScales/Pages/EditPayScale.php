<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\PayScales\Pages;

use AcMarche\Hrm\Filament\Resources\PayScales\PayScaleResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditPayScale extends EditRecord
{
    #[Override]
    protected static string $resource = PayScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->icon(Heroicon::Eye),
        ];
    }
}
