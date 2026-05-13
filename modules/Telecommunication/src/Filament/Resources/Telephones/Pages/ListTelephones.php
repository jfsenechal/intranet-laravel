<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\Telephones\Pages;

use AcMarche\Telecommunication\Filament\Resources\Telephones\TelephoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Override;

final class ListTelephones extends ListRecords
{
    #[Override]
    protected static string $resource = TelephoneResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Nouveau téléphone')
                ->icon(Heroicon::Plus),
        ];
    }
}
