<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Folders\Pages;

use AcMarche\Agent\Filament\Resources\Folders\FolderResource;
use AcMarche\Agent\Models\Folder;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

final class EditFolder extends EditRecord
{
    #[Override]
    protected static string $resource = FolderResource::class;

    #[Override]
    public function getTitle(): string|Htmlable
    {
        /** @var Folder $record */
        $record = $this->getRecord();

        return self::buildBreadcrumb($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }

    private static function buildBreadcrumb(Folder $folder): string
    {
        $segments = [];
        $current = $folder;
        while ($current !== null) {
            array_unshift($segments, $current->name);
            $current = $current->parent;
        }

        return implode(' / ', $segments);
    }
}
