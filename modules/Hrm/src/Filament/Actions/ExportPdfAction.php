<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Actions;

use AcMarche\Hrm\Filament\Exports\EmployeePdfExport;
use AcMarche\Hrm\Models\Employee;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Support\Icons\Heroicon;
use Spatie\LaravelPdf\PdfBuilder;

final class ExportPdfAction
{
    public static function make(): Action
    {
        return Action::make('exportPdf')
            ->label('Exporter en PDF')
            ->icon(Heroicon::ArrowDownTray)
            ->color('info')
            ->modalHeading('Exporter la fiche agent en PDF')
            ->modalSubmitActionLabel('Télécharger le PDF')
            ->schema([
                CheckboxList::make('relations')
                    ->label('Inclure les données liées')
                    ->helperText('Sélectionnez les sections à inclure dans le PDF.')
                    ->options([
                        'contracts' => 'Contrats',
                        'absences' => 'Absences',
                        'trainings' => 'Formations',
                        'evaluations' => 'Évaluations',
                        'diplomas' => 'Diplômes',
                        'internships' => 'Stages',
                        'valorizations' => 'Valorisations',
                        'deadlines' => 'Échéances',
                        'applications' => 'Candidatures',
                        'documents' => 'Documents',
                    ])
                    ->columns(2),
            ])
            ->action(function (array $data, Employee $record): PdfBuilder {
                return EmployeePdfExport::download($record, $data['relations'] ?? []);
            });
    }
}
