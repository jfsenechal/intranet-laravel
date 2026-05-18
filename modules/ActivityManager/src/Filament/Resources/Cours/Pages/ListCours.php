<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Cours\Pages;

use AcMarche\ActivityManager\Filament\Resources\Cours\CoursResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListCours extends ListRecords
{
    #[Override]
    protected static string $resource = CoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau cours')
                ->icon(Heroicon::Plus),
        ];
    }
}
