<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses\Pages;

use AcMarche\Offenses\Filament\Resources\Offenses\OffenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListOffenses extends ListRecords
{
    #[Override]
    protected static string $resource = OffenseResource::class;

    public function getTitle(): string
    {
        return 'Sanctions administratives';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouvelle sanction')
                ->icon('tabler-plus'),
        ];
    }
}
