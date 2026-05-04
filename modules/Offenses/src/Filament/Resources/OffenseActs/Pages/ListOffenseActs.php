<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\OffenseActs\Pages;

use AcMarche\Offenses\Filament\Resources\OffenseActs\OffenseActResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListOffenseActs extends ListRecords
{
    #[Override]
    protected static string $resource = OffenseActResource::class;

    public function getTitle(): string
    {
        return "Types d'actes";
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label("Nouveau type d'acte")
                ->icon('tabler-plus'),
        ];
    }
}
