<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Notes\Pages;

use AcMarche\MealDelivery\Filament\Resources\Notes\NoteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListNotes extends ListRecords
{
    #[Override]
    protected static string $resource = NoteResource::class;

    public function getTitle(): string
    {
        return 'Client notes';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add note')
                ->icon('tabler-plus'),
        ];
    }
}
