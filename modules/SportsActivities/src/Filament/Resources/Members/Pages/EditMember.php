<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Members\Pages;

use AcMarche\SportsActivities\Filament\Resources\Members\MemberResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditMember extends EditRecord
{
    #[Override]
    protected static string $resource = MemberResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Voir')
                ->icon(Heroicon::Eye),
        ];
    }
}
