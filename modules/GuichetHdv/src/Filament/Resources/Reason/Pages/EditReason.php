<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Reason\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Reason\ReasonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditReason extends EditRecord
{
    #[Override]
    protected static string $resource = ReasonResource::class;

    public function getTitle(): string
    {
        return 'Modifier le motif';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
