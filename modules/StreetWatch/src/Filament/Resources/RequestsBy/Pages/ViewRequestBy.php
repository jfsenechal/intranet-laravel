<?php

declare(strict_types=1);

namespace AcMarche\StreetWatch\Filament\Resources\RequestsBy\Pages;

use AcMarche\StreetWatch\Filament\Resources\RequestsBy\RequestByResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewRequestBy extends ViewRecord
{
    #[Override]
    protected static string $resource = RequestByResource::class;

    public function getTitle(): string
    {
        return (string) $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nom'),
                    ]),
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
