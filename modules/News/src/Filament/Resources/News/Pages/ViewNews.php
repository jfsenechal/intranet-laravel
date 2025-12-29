<?php

namespace AcMarche\News\Filament\Resources\News\Pages;

use AcMarche\News\Filament\Resources\News\NewsResource;
use AcMarche\News\Filament\Resources\News\Schemas\NewsInfolist;
use AcMarche\News\Models\News;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;

final class ViewNews extends ViewRecord
{
    protected static string $resource = NewsResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return NewsInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\Action::make('archive')
                ->icon('tabler-archive')
                ->label('Archiver')
                ->color(Color::Slate)
                ->action(fn (News $news) => $news->archive = true),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
        ];
    }
}
