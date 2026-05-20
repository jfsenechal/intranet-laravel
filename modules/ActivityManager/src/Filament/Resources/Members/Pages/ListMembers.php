<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Members\Pages;

use AcMarche\ActivityManager\Filament\Resources\Members\MembersResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListMembers extends ListRecords
{
    #[Override]
    protected static string $resource = MembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau membre')
                ->icon(Heroicon::Plus),
        ];
    }
}
