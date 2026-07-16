<?php

namespace AcMarche\Courrier\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Colors\Color;

class DownloadAction
{
    public static function make(): Action
    {
        return Action::make('download')
            ->label('Télécharger la pièce jointe')
            ->icon('tabler-download')
            ->color(Color::Green)
            ->url(fn($record): ?string => $record->attachments->isNotEmpty()
                ? route('courrier.attachments.download', $record->attachments->first())
                : null)
            ->visible(fn($record): bool => $record->attachments->isNotEmpty()
                && auth()->user()?->can('download', $record->attachments->first()));
    }
}
