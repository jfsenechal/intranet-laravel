<?php

declare(strict_types=1);

namespace AcMarche\CpasLibrary\Filament\Resources\Tags\Pages;

use AcMarche\CpasLibrary\Filament\Resources\Tags\TagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListTags extends ListRecords
{
    #[Override]
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau tag')
                ->icon(Heroicon::Plus),
        ];
    }
}
