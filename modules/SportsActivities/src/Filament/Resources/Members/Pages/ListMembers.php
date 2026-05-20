<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Members\Pages;

use AcMarche\SportsActivities\Filament\Resources\Members\MemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListMembers extends ListRecords
{
    #[Override]
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau sportif')
                ->icon(Heroicon::Plus),
        ];
    }
}
