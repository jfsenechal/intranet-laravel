<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Processes\Pages;

use AcMarche\Hrm\Filament\Resources\Processes\ProcessResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditProcess extends EditRecord
{
    #[Override]
    protected static string $resource = ProcessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
