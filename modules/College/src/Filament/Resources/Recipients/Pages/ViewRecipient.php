<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Recipients\Pages;

use AcMarche\College\Filament\Resources\Recipients\RecipientResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewRecipient extends ViewRecord
{
    #[Override]
    protected static string $resource = RecipientResource::class;

    public function getTitle(): string
    {
        return $this->record->nom.' '.$this->record->prenom;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nom')->label('Nom'),
                        TextEntry::make('prenom')->label('Prénom'),
                        TextEntry::make('email')->label('Email')->placeholder('—'),
                        TextEntry::make('slugname')->label('Slug')->placeholder('—'),
                    ]),

                Section::make('Convocations')
                    ->columns(2)
                    ->schema([
                        IconEntry::make('pv_service')->label('PV Service')->boolean(),
                        IconEntry::make('ordre_service')->label('Ordre du jour - Service')->boolean(),
                        IconEntry::make('ordre_college')->label('Ordre du jour - Collège')->boolean(),
                        IconEntry::make('pv_college')->label('PV Collège')->boolean(),
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
