<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\Complainants\Pages;

use AcMarche\Mediation\Filament\Resources\Complainants\ComplainantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListComplainants extends ListRecords
{
    #[Override]
    protected static string $resource = ComplainantResource::class;

    public function getTitle(): string
    {
        return 'Plaignants';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau plaignant')
                ->icon('tabler-plus'),
        ];
    }
}
