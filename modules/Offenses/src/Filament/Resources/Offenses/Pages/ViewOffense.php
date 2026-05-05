<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenses\Pages;

use AcMarche\Offenses\Filament\Resources\Offenses\OffenseResource;
use AcMarche\Offenses\Models\Offense;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Override;

final class ViewOffense extends ViewRecord
{
    #[Override]
    protected static string $resource = OffenseResource::class;

    public function getTitle(): string
    {
        return 'Incivilité: '.$this->record->offenseAct->name;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('download')
                ->label('Télécharger le document')
                ->icon('tabler-download')
                ->color(Color::Green)
                ->visible(fn(Offense $record) => $record->file_name)
                ->url(fn(Offense $record) => Storage::disk('public')->url($record->file_name)),
            EditAction::make()->icon(Heroicon::Pencil),
            DeleteAction::make()->icon(Heroicon::Trash),
        ];
    }
}
