<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\FollowUp\Pages;

use AcMarche\Pst\Filament\Resources\FollowUp\FollowUpResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Override;

final class ViewFollowUp extends ViewRecord
{
    #[Override]
    protected static string $resource = FollowUpResource::class;

    public function getTitle(): string
    {
        return $this->record->property ?? 'Empty name';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
