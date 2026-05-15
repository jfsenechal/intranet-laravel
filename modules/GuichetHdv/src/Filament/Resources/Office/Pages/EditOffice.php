<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Office\Pages;

use AcMarche\GuichetHdv\Filament\Resources\Office\OfficeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditOffice extends EditRecord
{
    #[Override]
    protected static string $resource = OfficeResource::class;

    public function getTitle(): string
    {
        return 'Modifier: '.$this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
