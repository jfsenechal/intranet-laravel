<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Rsses\Pages;

use AcMarche\App\Filament\Resources\Rsses\RssResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditRss extends EditRecord
{
    #[Override]
    protected static string $resource = RssResource::class;

    public function getTitle(): string
    {
        return 'Modifier le flux RSS';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
