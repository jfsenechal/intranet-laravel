<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Destinataires\Pages;

use AcMarche\Conseil\Filament\Resources\Destinataires\DestinataireResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewDestinataire extends ViewRecord
{
    #[Override]
    protected static string $resource = DestinataireResource::class;

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
