<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Minutes\Pages;

use AcMarche\Conseil\Filament\Resources\Minutes\MinuteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListMinutes extends ListRecords
{
    #[Override]
    protected static string $resource = MinuteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau procès-verbal')
                ->icon(Heroicon::Plus),
        ];
    }
}
