<?php

namespace AcMarche\Mileage\Filament\Resources\Declarations\Pages;

use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use AcMarche\Mileage\Filament\Resources\Declarations\Schemas\DeclarationInfolist;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Pdf\PdfFactory;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Illuminate\Contracts\Support\Htmlable;

final class ViewDeclaration extends ViewRecord
{
    protected static string $resource = DeclarationResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Déclaration num '.$this->record->id.' ('.$this->record->type_movement.')';
    }

    public function infolist(Schema $schema): Schema
    {
        return DeclarationInfolist::configure($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Télécharger')
                ->icon('tabler-download')
                ->color(Color::Green)
                ->action(function (Declaration $record) {

                    $pdfFactory = new PdfFactory();
                    $pdf = $pdfFactory->createFromDeclaration($record);

                    Notification::make()
                        ->title('Pdf exporté')
                        ->success()
                        ->send();

                    return response()->download($pdf['path'], $pdf['name'], [
                        'Content-Type' => 'application/pdf',
                    ]);
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
