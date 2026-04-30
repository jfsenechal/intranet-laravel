<?php

declare(strict_types=1);

namespace AcMarche\Publication\Filament\Resources\Publications\Pages;

use AcMarche\Publication\Filament\Resources\Publications\PublicationResource;
use AcMarche\Publication\Models\Publication;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Override;

final class ViewPublication extends ViewRecord
{
    #[Override]
    protected static string $resource = PublicationResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Voir la publication')
                ->icon('tabler-eye')
                ->color(Color::Green)
                ->url(fn (Publication $publication) => $publication->url, true),
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
