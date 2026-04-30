<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Declarations\Pages;

use AcMarche\Mileage\Factory\PdfFactory;
use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use AcMarche\Mileage\Filament\Resources\Declarations\Schemas\DeclarationInfolist;
use AcMarche\Mileage\Models\Declaration;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Override;

final class ViewDeclaration extends ViewRecord
{
    #[Override]
    protected static string $resource = DeclarationResource::class;

    public function getTitle(): string
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
            Action::make('download')
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
            Action::make('back')
                ->label('Retour à la liste')
                ->icon('tabler-list')
                ->url(DeclarationResource::getUrl('index')),
            EditAction::make()
                ->icon('tabler-edit'),
            DeleteAction::make()
                ->icon('tabler-trash'),
        ];
    }
}
