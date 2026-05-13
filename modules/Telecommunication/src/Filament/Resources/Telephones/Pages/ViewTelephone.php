<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Filament\Resources\Telephones\Pages;

use AcMarche\Telecommunication\Filament\Resources\Telephones\TelephoneResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewTelephone extends ViewRecord
{
    #[Override]
    protected static string $resource = TelephoneResource::class;

    public function getTitle(): string
    {
        return $this->record->user_name.' — '.$this->record->number;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identification')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user_name')->label('Utilisateur'),
                        TextEntry::make('number')->label('Numéro'),
                        TextEntry::make('lineType.name')->label('Type de ligne')->badge(),
                        IconEntry::make('archived')->label('Archivé')->boolean(),
                    ]),
                Section::make('Opérateurs')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('mobistar')->label('Mobistar')->placeholder('—'),
                        TextEntry::make('proximus')->label('Proximus')->placeholder('—'),
                    ]),
                Section::make('Affectation')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('service')->label('Service')->placeholder('—'),
                        TextEntry::make('department')->label('Département')->placeholder('—'),
                        TextEntry::make('location')->label('Localisation')->placeholder('—'),
                        TextEntry::make('budget_article')->label('Article budgétaire')->placeholder('—'),
                        TextEntry::make('fixed_cost')->label('Coût fixe')->placeholder('—'),
                    ]),
                Section::make('Note')
                    ->schema([
                        TextEntry::make('note')->label('Note')->placeholder('—')->columnSpanFull(),
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
