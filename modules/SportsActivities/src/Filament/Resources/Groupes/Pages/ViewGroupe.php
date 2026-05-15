<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groupes\Pages;

use AcMarche\SportsActivities\Filament\Resources\Groupes\GroupeResource;
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
        return $this->record->jour.' — '.$this->record->lieux;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Groupe')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('activite.nom')->label('Activité'),
                        TextEntry::make('jour')->label('Jour'),
                        TextEntry::make('heure')->label('Heure'),
                        TextEntry::make('lieux')->label('Lieu'),
                        TextEntry::make('age')->label('Âge'),
                        TextEntry::make('prix')->label('Prix')->money('EUR'),
                        TextEntry::make('description')->label('Description')->columnSpanFull(),
                        TextEntry::make('remarque')->label('Remarque')->columnSpanFull(),
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
