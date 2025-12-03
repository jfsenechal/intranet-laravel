<?php

namespace AcMarche\Mileage\Filament\Resources\DeclarationResource\Pages;

use AcMarche\Mileage\Filament\Resources\DeclarationResource;
use AcMarche\Mileage\Models\Declaration;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;

final class ViewDeclaration extends ViewRecord
{
    protected static string $resource = DeclarationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Télécharger le document')
                ->icon('tabler-download')
                ->color(Color::Green)->url(fn (Declaration $record) => route('download.action', $record))
                ->action(function (Declaration $record) {
                    Notification::make()
                        ->title('Pdf exporté')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('back')
                ->label('Retour à la liste')
                ->icon('tabler-list')
                ->url(DeclarationResource::getUrl('index')),
            Actions\EditAction::make()
                ->icon('tabler-edit'),
            Actions\DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
