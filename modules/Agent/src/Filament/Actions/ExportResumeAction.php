<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Actions;

use AcMarche\Agent\Filament\Exports\ProfilePdfExport;
use AcMarche\Agent\Models\Profile;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class ExportResumeAction
{
    public static function make(): Action
    {
        return Action::make('exportResume')
            ->label('Résumé de la fiche')
            ->icon(Heroicon::ArrowDownTray)
            ->color('info')
            ->action(fn (Profile $record): StreamedResponse => ProfilePdfExport::downloadResume($record));
    }
}
