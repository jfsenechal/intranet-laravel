<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groups\Pages;

use AcMarche\SportsActivities\Filament\Resources\Groups\GroupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewGroup extends ViewRecord
{
    #[Override]
    protected static string $resource = GroupResource::class;

    public function getTitle(): string
    {
        return $this->record->day.' — '.$this->record->location;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Groupe')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('activity.name')->label('Activité'),
                        TextEntry::make('day')->label('Jour'),
                        TextEntry::make('time')->label('Heure'),
                        TextEntry::make('location')->label('Lieu'),
                        TextEntry::make('age')->label('Âge'),
                        TextEntry::make('price')->label('Prix')->money('EUR'),
                        TextEntry::make('description')->label('Description')->columnSpanFull(),
                        TextEntry::make('comment')->label('Remarque')->columnSpanFull(),
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
