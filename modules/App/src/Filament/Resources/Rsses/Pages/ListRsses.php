<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Rsses\Pages;

use AcMarche\App\Filament\Resources\Rsses\RssResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListRsses extends ListRecords
{
    #[Override]
    protected static string $resource = RssResource::class;

    public function getTitle(): string
    {
        return 'Mes flux RSS';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
