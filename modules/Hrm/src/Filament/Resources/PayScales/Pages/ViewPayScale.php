<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\PayScales\Pages;

use AcMarche\Hrm\Filament\Resources\PayScales\PayScaleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewPayScale extends ViewRecord
{
    #[Override]
    protected static string $resource = PayScaleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon(Heroicon::Pencil),
            DeleteAction::make()
                ->icon(Heroicon::Trash),

        ];
    }
}
