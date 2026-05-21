<?php

declare(strict_types=1);

namespace AcMarche\Conseil\Filament\Resources\Agendas\Pages;

use AcMarche\Conseil\Filament\Resources\Agendas\AgendaResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Override;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ViewAgenda extends ViewRecord
{
    #[Override]
    protected static string $resource = AgendaResource::class;

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
                        TextEntry::make('agenda_date')
                            ->label('Date de l\'ordre du jour')
                            ->dateTime(),
                        TextEntry::make('distribution_end_date')
                            ->label('Date de fin de diffusion')
                            ->date()
                            ->placeholder('—'),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Télécharger le fichier')
                ->icon(Heroicon::ArrowDownTray)
                ->color(Color::Yellow)
                ->visible(fn (): bool => filled($this->record->file_name)
                    && Storage::disk('public')->exists($this->record->file_name))
                ->action(fn (): StreamedResponse => Storage::disk('public')->download(
                    $this->record->file_name,
                )),
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
