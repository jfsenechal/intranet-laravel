<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Members\Pages;

use AcMarche\SportsActivities\Filament\Resources\Members\MemberResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewMember extends ViewRecord
{
    #[Override]
    protected static string $resource = MemberResource::class;

    public function getTitle(): string
    {
        return $this->record->first_name.' '.$this->record->last_name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identité')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('last_name')->label('Nom'),
                        TextEntry::make('first_name')->label('Prénom'),
                        TextEntry::make('birth_date')->label('Date de naissance')->date(),
                    ]),

                Section::make('Adresse')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('street')->label('Rue')->columnSpanFull(),
                        TextEntry::make('postal_code')->label('Code postal'),
                        TextEntry::make('city')->label('Localité'),
                    ]),

                Section::make('Contact')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('phone')->label('Téléphone'),
                        TextEntry::make('mobile')->label('GSM'),
                        TextEntry::make('email')->label('Email')->columnSpanFull(),
                    ]),

                Section::make('Remarque')
                    ->schema([
                        TextEntry::make('comment')->label('Remarque'),
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
