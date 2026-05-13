<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\LineTypes\Pages;

use AcMarche\Telecommunication\Filament\Resources\LineTypes\LineTypeResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditLineType extends EditRecord
{
    #[Override]
    protected static string $resource = LineTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
