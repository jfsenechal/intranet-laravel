<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Groupes\Pages;

use AcMarche\Conseil\Filament\Resources\Groupes\GroupeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewGroupe extends ViewRecord
{
    #[Override]
    protected static string $resource = GroupeResource::class;

    public function getTitle(): string
    {
        return $this->record->nom;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nom')->label('Nom'),
                        TextEntry::make('destinataires_count')
                            ->label('Destinataires')
                            ->state(fn ($record): int => $record->destinataires()->count()),
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
