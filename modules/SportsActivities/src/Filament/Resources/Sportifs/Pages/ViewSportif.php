<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Sportifs\Pages;

use AcMarche\SportsActivities\Filament\Resources\Sportifs\SportifResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewSportif extends ViewRecord
{
    #[Override]
    protected static string $resource = SportifResource::class;

    public function getTitle(): string
    {
        return $this->record->prenom.' '.$this->record->nom;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nom')->label('Nom'),
                        TextEntry::make('prenom')->label('Prénom'),
                        TextEntry::make('ne_le')->label('Date de naissance')->date(),
                    ]),

                Section::make('Adresse')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('rue')->label('Rue')->columnSpanFull(),
                        TextEntry::make('code_postal')->label('Code postal'),
                        TextEntry::make('localite')->label('Localité'),
                    ]),

                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('telephone')->label('Téléphone'),
                        TextEntry::make('gsm')->label('GSM'),
                        TextEntry::make('email')->label('Email')->columnSpanFull(),
                    ]),

                Section::make('Remarque')
                    ->schema([
                        TextEntry::make('remarque')->label('Remarque'),
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
