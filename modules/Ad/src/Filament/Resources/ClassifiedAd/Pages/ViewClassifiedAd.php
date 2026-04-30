<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages;

use AcMarche\Ad\Filament\Resources\ClassifiedAd\ClassifiedAdResource;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\Schemas\ClassifiedAdInfolist;
use AcMarche\Ad\Models\ClassifiedAd;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Override;

final class ViewClassifiedAd extends ViewRecord
{
    #[Override]
    protected static string $resource = ClassifiedAdResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return ClassifiedAdInfolist::configure($schema);
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
                ->action(fn (ClassifiedAd $classifiedAd): true => $classifiedAd->archive = true),
            DeleteAction::make()
                ->icon('tabler-trash'),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }
}
