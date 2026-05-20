<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Activities\Pages;

use AcMarche\ActivityManager\Filament\Resources\Activities\ActivityResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditActivity extends EditRecord
{
    #[Override]
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
