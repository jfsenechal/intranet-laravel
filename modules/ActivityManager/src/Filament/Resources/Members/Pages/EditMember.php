<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Members\Pages;

use AcMarche\ActivityManager\Filament\Resources\Members\MembersResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditMember extends EditRecord
{
    #[Override]
    protected static string $resource = MembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
