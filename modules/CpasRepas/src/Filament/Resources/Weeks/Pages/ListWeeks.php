<?php

declare(strict_types=1);

namespace AcMarche\CpasRepas\Filament\Resources\Weeks\Pages;

use AcMarche\CpasRepas\Filament\Resources\Weeks\WeekResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListWeeks extends ListRecords
{
    #[Override]
    protected static string $resource = WeekResource::class;

    public function getTitle(): string
    {
        return 'Weeks';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add week')
                ->icon('tabler-plus'),
        ];
    }
}
