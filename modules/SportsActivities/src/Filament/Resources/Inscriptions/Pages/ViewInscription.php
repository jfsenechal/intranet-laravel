<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Inscriptions\Pages;

use AcMarche\SportsActivities\Filament\Resources\Inscriptions\InscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewInscription extends ViewRecord
{
    #[Override]
    protected static string $resource = InscriptionResource::class;

    public function getTitle(): string
    {
        return 'Inscription #'.$this->record->id;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Inscription')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('sportif.nom')->label('Nom'),
                        TextEntry::make('sportif.prenom')->label('Prénom'),
                        TextEntry::make('activite.nom')->label('Activité'),
                        TextEntry::make('groupe.jour')->label('Jour'),
                        TextEntry::make('groupe.heure')->label('Heure'),
                        TextEntry::make('groupe.lieux')->label('Lieu'),
                        TextEntry::make('prix')->label('Prix')->money('EUR'),
                        TextEntry::make('created_at')->label('Inscription')->date(),
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
