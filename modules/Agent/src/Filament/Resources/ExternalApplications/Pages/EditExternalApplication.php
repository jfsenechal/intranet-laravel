<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\ExternalApplications\Pages;

use AcMarche\Agent\Filament\Resources\ExternalApplications\ExternalApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditExternalApplication extends EditRecord
{
    #[Override]
    protected static string $resource = ExternalApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
