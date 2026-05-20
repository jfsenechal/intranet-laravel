<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Members\Pages;

use AcMarche\ActivityManager\Filament\Resources\Members\MembersResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewMember extends ViewRecord
{
    #[Override]
    protected static string $resource = MembersResource::class;

    public function getTitle(): string
    {
        return $this->record->nom.' '.$this->record->prenom;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('civilite')->label('Civilité')->badge()->placeholder('—'),
                        IconEntry::make('enabled')->label('Actif')->boolean(),
                        TextEntry::make('nom')->label('Nom')->weight('bold'),
                        TextEntry::make('prenom')->label('Prénom'),
                        TextEntry::make('inscrit_le')->label('Inscrit le')->date('d/m/Y')->placeholder('—'),
                    ]),

                Section::make('Adresse')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('rue')->label('Rue')->columnSpan(2)->placeholder('—'),
                        TextEntry::make('numero')->label('N°')->placeholder('—'),
                        TextEntry::make('codepostal')->label('Code postal')->placeholder('—'),
                        TextEntry::make('localite')->label('Localité')->columnSpan(2)->placeholder('—'),
                    ]),

                Section::make('Contact')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('gsm')->label('GSM')->copyable()->placeholder('—'),
                        TextEntry::make('telephone')->label('Téléphone')->copyable()->placeholder('—'),
                        TextEntry::make('email')->label('Email')->copyable()->placeholder('—'),
                    ]),

                Section::make('Notes')
                    ->schema([
                        TextEntry::make('remarque')->label('Remarque')->columnSpanFull()->placeholder('—'),
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
