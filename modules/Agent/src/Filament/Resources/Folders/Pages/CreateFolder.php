<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Resources\Folders\Pages;

use AcMarche\Agent\Filament\Resources\Folders\FolderResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateFolder extends CreateRecord
{
    #[Override]
    protected static string $resource = FolderResource::class;

    protected static ?string $title = 'Ajouter un dossier';
}
