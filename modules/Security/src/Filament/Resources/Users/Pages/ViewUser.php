<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Users\Pages;

use AcMarche\Security\Filament\Resources\Users\UserResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Override;
use STS\FilamentImpersonate\Actions\Impersonate;

final class ViewUser extends ViewRecord
{
    #[Override]
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        return $this->record->full_name ?? 'Empty name';
    }

    protected function getHeaderActions(): array
    {
        return [
            Impersonate::make()
                ->color('success')
                ->record($this->getRecord()),
            EditAction::make()
                ->icon(Heroicon::Pencil),
        ];
    }
}
