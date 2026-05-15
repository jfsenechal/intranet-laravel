<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Pvs\Pages;

use AcMarche\Conseil\Filament\Resources\Pvs\PvResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewPv extends ViewRecord
{
    #[Override]
    protected static string $resource = PvResource::class;

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
                        TextEntry::make('date_pv')->label('Date du procès-verbal')->date(),
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
