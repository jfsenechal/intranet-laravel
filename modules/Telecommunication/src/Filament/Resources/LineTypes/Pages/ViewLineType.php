<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\LineTypes\Pages;

use AcMarche\Telecommunication\Filament\Resources\LineTypes\LineTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewLineType extends ViewRecord
{
    #[Override]
    protected static string $resource = LineTypeResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')->label('Nom'),
                TextEntry::make('slug')->label('Identifiant'),
                TextEntry::make('telephones_count')
                    ->label('Téléphones')
                    ->state(fn ($record): int => $record->telephones()->count()),
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
