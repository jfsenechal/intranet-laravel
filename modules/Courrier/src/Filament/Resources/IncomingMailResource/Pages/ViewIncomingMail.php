<?php

namespace AcMarche\Courrier\Filament\Resources\IncomingMailResource\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMailResource;
use AcMarche\Courrier\Filament\Resources\IncomingMailResource\Schema\IncomingMailInfolist;
use AcMarche\Courrier\Models\IncomingMail;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Storage;

final class ViewIncomingMail extends ViewRecord
{
    protected static string $resource = IncomingMailResource::class;

    public function getTitle(): string
    {
        return $this->record->reference;
    }

    public function infolist(Schema $schema): Schema
    {
        return IncomingMailInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Télécharger la pièce jointe')
                ->icon('tabler-download')
                ->color(Color::Green)
                ->url(fn (IncomingMail $record) => Storage::disk('public')->url($record->attachment_path))
                ->visible(fn (IncomingMail $record): bool => ! blank($record->attachment_path)),
            Actions\Action::make('back')
                ->label('Retour à la liste')
                ->icon('tabler-list')
                ->url(IncomingMailResource::getUrl('index')),
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
