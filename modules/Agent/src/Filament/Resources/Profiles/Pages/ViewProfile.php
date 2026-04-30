<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Profiles\Pages;

use AcMarche\Agent\Filament\Resources\Profiles\ProfileResource;
use AcMarche\Agent\Filament\Resources\Profiles\Schemas\ProfileInfolist;
use AcMarche\Agent\Models\Profile;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewProfile extends ViewRecord
{
    #[Override]
    protected static string $resource = ProfileResource::class;

    public function getTitle(): string
    {
        /** @var Profile $record */
        $record = $this->record;

        return 'Profil de '.$record->fullName() ?? $record->username;
    }

    public function infolist(Schema $schema): Schema
    {
        return ProfileInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->icon(Heroicon::Trash),
        ];
    }
}
