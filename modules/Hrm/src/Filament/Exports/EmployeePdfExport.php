<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\Employee;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\PdfBuilder;

use function Spatie\LaravelPdf\Support\pdf;

final class EmployeePdfExport
{
    /**
     * @param  list<string>  $relations
     */
    public static function download(Employee $employee, array $relations): PdfBuilder
    {
        $eagerLoad = array_filter($relations);

        if (in_array('contracts', $eagerLoad, true)) {
            $eagerLoad[] = 'contracts.employer';
            $eagerLoad[] = 'contracts.direction';
            $eagerLoad[] = 'contracts.service';
            $eagerLoad[] = 'contracts.contractNature';
            $eagerLoad[] = 'contracts.contractType';
            $eagerLoad = array_filter($eagerLoad, fn (string $r) => $r !== 'contracts');
        }

        if (in_array('evaluations', $eagerLoad, true)) {
            $eagerLoad[] = 'evaluations.direction';
            $eagerLoad = array_filter($eagerLoad, fn (string $r) => $r !== 'evaluations');
        }

        $employee->loadMissing(array_values($eagerLoad));

        return pdf()
            ->view('hrm::pdf.employee', [
                'employee' => $employee,
                'selectedRelations' => $relations,
            ])
            ->withBrowsershot(function (Browsershot $browsershot): void {
                if ($path = config('pdf.node_modules_path')) {
                    $browsershot->setNodeModulePath($path);
                }
                if ($path = config('pdf.chrome_path')) {
                    $browsershot->setChromePath($path);
                }
            })
            ->name($employee->last_name.'-'.$employee->first_name.'.pdf')
            ->download();
    }
}
