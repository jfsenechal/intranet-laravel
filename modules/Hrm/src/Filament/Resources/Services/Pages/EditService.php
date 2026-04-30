<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Services\Pages;

use AcMarche\Hrm\Filament\Resources\Services\ServiceResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditService extends EditRecord
{
    #[Override]
    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->icon(Heroicon::Eye),
        ];
    }
}
