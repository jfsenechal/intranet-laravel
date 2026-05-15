<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\OrdresJour\Pages;

use AcMarche\Conseil\Filament\Resources\OrdresJour\OrdreJourResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewOrdreJour extends ViewRecord
{
    #[Override]
    protected static string $resource = OrdreJourResource::class;

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
                        TextEntry::make('date_ordre')->label('Date de l\'ordre du jour')->dateTime(),
                        TextEntry::make('date_fin_diffusion')->label('Date de fin de diffusion')->date()->placeholder('—'),
                        TextEntry::make('file_name')->label('Fichier')->placeholder('—'),
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
