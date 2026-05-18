<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Cours\Pages;

use AcMarche\ActivityManager\Filament\Resources\Cours\CoursResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditCours extends EditRecord
{
    #[Override]
    protected static string $resource = CoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
