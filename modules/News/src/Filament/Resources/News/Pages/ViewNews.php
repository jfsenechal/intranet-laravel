<?php

declare(strict_types=1);

namespace AcMarche\News\Filament\Resources\News\Pages;

use AcMarche\News\Filament\Resources\News\NewsResource;
use AcMarche\News\Filament\Resources\News\Schemas\NewsInfolist;
use AcMarche\News\Models\News;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Override;

final class ViewNews extends ViewRecord
{
    #[Override]
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
            EditAction::make()
                ->icon('tabler-edit'),
            Action::make('archive')
                ->icon('tabler-archive')
                ->label('Archiver')
                ->color(Color::Slate)
                ->action(fn (News $news): true => $news->archive = true),
            DeleteAction::make()
                ->icon('tabler-trash'),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
