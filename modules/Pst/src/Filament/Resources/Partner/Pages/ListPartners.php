<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Partner\Pages;

use AcMarche\Pst\Filament\Resources\Partner\PartnerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListPartners extends ListRecords
{
    #[Override]
    protected static string $resource = PartnerResource::class;

    public function getTitle(): string
    {
        return $this->getAllTableRecordsCount().' partenaires';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Ajouter un partenaire')
                ->icon('tabler-plus'),
        ];
    }
}
