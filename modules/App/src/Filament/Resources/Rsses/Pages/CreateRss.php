<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Rsses\Pages;

use AcMarche\App\Filament\Resources\Rsses\RssResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Override;

final class CreateRss extends CreateRecord
{
    #[Override]
    protected static string $resource = RssResource::class;

    public function getTitle(): string
    {
        return 'Ajouter un flux RSS';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usernme'] = Auth::user()->username;

        return $data;
    }
}
