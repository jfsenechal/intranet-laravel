<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders\Pages;

use AcMarche\Offenses\Filament\Resources\Offenders\OffenderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListOffenders extends ListRecords
{
    #[Override]
    protected static string $resource = OffenderResource::class;

    public function getTitle(): string
    {
        return 'Contrevenants';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau contrevenant')
                ->icon('tabler-plus'),
        ];
    }
}
