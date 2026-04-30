<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Actions;

use AcMarche\MailingList\Filament\Imports\ContactImporter;
use Filament\Actions\ImportAction;
use Filament\Support\Icons\Heroicon;

final class ImportContactAction
{
    public static function make(): ImportAction
    {
        return
            ImportAction::make()
                ->importer(ContactImporter::class)
                ->label('Importer des contacts')
                ->modalDescription('Le séparateur de champ est la virgule (",").')
                ->icon(Heroicon::ArrowUpTray)
                ->csvDelimiter(',');
    }
}
