<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Signatures\Pages;

use AcMarche\App\Filament\Resources\Signatures\SignatureResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Override;

final class EditSignature extends EditRecord
{
    #[Override]
    protected static string $resource = SignatureResource::class;

    public function getTitle(): string
    {
        return 'Modifier ma signature';
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()->icon(Heroicon::Eye),
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
