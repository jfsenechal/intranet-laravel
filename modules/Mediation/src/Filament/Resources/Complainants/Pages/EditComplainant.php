<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\Complainants\Pages;

use AcMarche\Mediation\Filament\Resources\Complainants\ComplainantResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditComplainant extends EditRecord
{
    #[Override]
    protected static string $resource = ComplainantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->icon(Heroicon::Eye),
        ];
    }
}
