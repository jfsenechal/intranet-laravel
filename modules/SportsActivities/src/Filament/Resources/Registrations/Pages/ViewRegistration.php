<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Registrations\Pages;

use AcMarche\SportsActivities\Filament\Resources\Registrations\RegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewRegistration extends ViewRecord
{
    #[Override]
    protected static string $resource = RegistrationResource::class;

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
                        TextEntry::make('member.last_name')->label('Nom'),
                        TextEntry::make('member.first_name')->label('Prénom'),
                        TextEntry::make('activity.name')->label('Activité'),
                        TextEntry::make('group.day')->label('Jour'),
                        TextEntry::make('group.time')->label('Heure'),
                        TextEntry::make('group.location')->label('Lieu'),
                        TextEntry::make('price')->label('Prix')->money('EUR'),
                        TextEntry::make('created_at')->label('Inscription')->date(),
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
