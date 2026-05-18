<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages;

use AcMarche\StreetWatch\Filament\Resources\RequestsBy\RequestByResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditRequestBy extends EditRecord
{
    #[Override]
    protected static string $resource = RequestByResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
