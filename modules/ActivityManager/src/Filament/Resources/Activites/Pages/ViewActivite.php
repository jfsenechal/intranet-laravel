<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Activites\Pages;

use AcMarche\ActivityManager\Filament\Resources\Activites\ActiviteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewActivite extends ViewRecord
{
    #[Override]
    protected static string $resource = ActiviteResource::class;

    public function getTitle(): string
    {
        return (string) $this->record->nom;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('nom')
                    ->label('Nom')
                    ->weight('bold'),
                TextEntry::make('description')
                    ->label('Description')
                    ->columnSpanFull()
                    ->placeholder('—'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
