<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Minutes\Pages;

use AcMarche\Conseil\Filament\Resources\Minutes\MinuteResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewMinute extends ViewRecord
{
    #[Override]
    protected static string $resource = MinuteResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informations')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label('Nom'),
                        TextEntry::make('meeting_date')->label('Date du procès-verbal')->date(),
                        TextEntry::make('file_name')->label('Fichier')->placeholder('—'),
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
