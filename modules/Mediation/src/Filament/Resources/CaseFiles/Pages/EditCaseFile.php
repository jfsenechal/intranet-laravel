<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\CaseFiles\Pages;

use AcMarche\Mediation\Filament\Resources\CaseFiles\CaseFileResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditCaseFile extends EditRecord
{
    #[Override]
    protected static string $resource = CaseFileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->icon(Heroicon::Eye),
        ];
    }
}
